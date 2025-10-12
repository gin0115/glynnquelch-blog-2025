#!/usr/bin/env python3
"""
Script to process tobacco review HTML files and create WordPress block markup
"""

import os
import re
import json
from pathlib import Path
from urllib.parse import urlparse
import shutil

def extract_blend_data(html_file):
    """Extract blend data from HTML file"""
    with open(html_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Extract original URL from comment
    url_match = re.search(r'<!-- saved from url=\([^)]+\)([^>]+) -->', content)
    original_url = url_match.group(1) if url_match else ""
    
    # Extract brand and blend name from title
    title_match = re.search(r'<title>Tobacco Reviews \| ([^-]+) - ([^<]+)</title>', content)
    if title_match:
        brand_name = title_match.group(1).strip()
        blend_name = title_match.group(2).strip()
    else:
        brand_name = "GQ Tobaccos"
        blend_name = "Unknown"
    
    # Extract overall rating
    rating_match = re.search(r'<span class="spanReviewNumber spanReviewAverage">([0-9.]+)</span>', content)
    overall_rating = float(rating_match.group(1)) if rating_match else 0.0
    
    # Extract total reviews
    reviews_match = re.search(r'(\d+) reviews', content)
    total_reviews = int(reviews_match.group(1)) if reviews_match else 0
    
    # Extract star counts
    star_counts = [0, 0, 0, 0]  # [1-star, 2-star, 3-star, 4-star]
    star_matches = re.findall(r'<span class="divReviewGraphCount">(\d+)</span>', content)
    if len(star_matches) >= 4:
        star_counts = [int(star_matches[3]), int(star_matches[2]), int(star_matches[1]), int(star_matches[0])]
    
    # Extract details from table
    details = {}
    
    # Series
    series_match = re.search(r'<td class="detailColumn1">Series</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['series'] = series_match.group(1).strip() if series_match else ""
    
    # Blended By
    blended_match = re.search(r'<td class="detailColumn1">Blended By</td>\s*<td class="detailColumn2">[^>]*>([^<]+)</td>', content)
    blended_by = blended_match.group(1).strip() if blended_match else "Glynn Quelch"
    
    # Handle special cases for blended by
    if "Glynn Quelch / Gauntleys" in blended_by:
        blended_by = "Glynn Quelch"
    elif blended_by == "Gauntleys" or blended_by == "Gauntleys Of Nottingham":
        # Skip this blend - only blended by Gauntleys
        return None
    
    details['blendedBy'] = blended_by
    
    # Manufactured By
    manufactured_match = re.search(r'<td class="detailColumn1">Manufactured By</td>\s*<td class="detailColumn2">[^>]*>([^<]+)</td>', content)
    manufactured_by = manufactured_match.group(1).strip() if manufactured_match else "GQ Tobaccos"
    
    # Handle special cases for manufactured by
    if "Gauntleys" in manufactured_by:
        manufactured_by = "Gauntleys of Nottingham"
    
    details['manufacturedBy'] = manufactured_by
    
    # Blend Type
    blend_type_match = re.search(r'<td class="detailColumn1">Blend Type</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['blendType'] = blend_type_match.group(1).strip() if blend_type_match else ""
    
    # Contents
    contents_match = re.search(r'<td class="detailColumn1">Contents</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['contents'] = contents_match.group(1).strip() if contents_match else ""
    
    # Flavoring
    flavoring_match = re.search(r'<td class="detailColumn1">Flavoring</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['flavoring'] = flavoring_match.group(1).strip() if flavoring_match else ""
    
    # Cut
    cut_match = re.search(r'<td class="detailColumn1">Cut</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['cut'] = cut_match.group(1).strip() if cut_match else ""
    
    # Country
    country_match = re.search(r'<td class="detailColumn1">Country</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['country'] = country_match.group(1).strip() if country_match else ""
    
    # Production
    production_match = re.search(r'<td class="detailColumn1">Production</td>\s*<td class="detailColumn2">([^<]+)</td>', content)
    details['productionStatus'] = production_match.group(1).strip() if production_match else "No longer in production"
    
    # Extract profile ratings
    profile_ratings = {}
    
    # Strength
    strength_match = re.search(r'<div class="row divProfileRatingName"> <strong>Strength</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    strength_value = strength_match.group(1).strip() if strength_match else ""
    # Skip if it's just "0" - this usually means no data
    profile_ratings['strength'] = strength_value if strength_value and strength_value != "0" else ""
    
    # Flavoring
    flavoring_rating_match = re.search(r'<div class="row divProfileRatingName"> <strong>Flavoring</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    flavoring_value = flavoring_rating_match.group(1).strip() if flavoring_rating_match else ""
    profile_ratings['flavoringRating'] = flavoring_value if flavoring_value and flavoring_value != "0" else ""
    
    # Room Note
    room_note_match = re.search(r'<div class="row divProfileRatingName"> <strong>Room Note</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    room_note_value = room_note_match.group(1).strip() if room_note_match else ""
    profile_ratings['roomNote'] = room_note_value if room_note_value and room_note_value != "0" else ""
    
    # Taste
    taste_match = re.search(r'<div class="row divProfileRatingName"> <strong>Taste</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    taste_value = taste_match.group(1).strip() if taste_match else ""
    profile_ratings['taste'] = taste_value if taste_value and taste_value != "0" else ""
    
    # Extract description
    description_match = re.search(r'<div class="detailDescription divBlendTinDescription">(.*?)</div>', content, re.DOTALL)
    description = ""
    if description_match:
        description = description_match.group(1).strip()
        # Clean up HTML entities and tags
        description = re.sub(r'<[^>]+>', '', description)
        description = description.replace('&amp;', '&').replace('&quot;', '"').replace('&#39;', "'")
        description = re.sub(r'\s+', ' ', description).strip()
    
    # Extract notes
    notes_match = re.search(r'<div class="detailDescription divBlendNotes"><strong>Notes: </strong>([^<]+)</div>', content)
    notes = notes_match.group(1).strip() if notes_match else ""
    
    # Extract image
    image_match = re.search(r'<img class="imgBlendDetail" src="([^"]+)"', content)
    image_url = image_match.group(1) if image_match else ""
    
    return {
        'original_url': original_url,
        'brandName': brand_name,
        'blendName': blend_name,
        'overallRating': overall_rating,
        'totalReviews': total_reviews,
        'star1Count': star_counts[0],
        'star2Count': star_counts[1], 
        'star3Count': star_counts[2],
        'star4Count': star_counts[3],
        'series': details.get('series', ''),
        'blendedBy': details.get('blendedBy', 'Glynn Quelch'),
        'manufacturedBy': details.get('manufacturedBy', 'GQ Tobaccos'),
        'blendType': details.get('blendType', ''),
        'contents': details.get('contents', ''),
        'flavoring': details.get('flavoring', ''),
        'cut': details.get('cut', ''),
        'country': details.get('country', ''),
        'productionStatus': details.get('productionStatus', 'No longer in production'),
        'strength': profile_ratings.get('strength', ''),
        'flavoringRating': profile_ratings.get('flavoringRating', ''),
        'roomNote': profile_ratings.get('roomNote', ''),
        'taste': profile_ratings.get('taste', ''),
        'description': description,
        'notes': notes,
        'imageUrl': image_url
    }

def create_block_markup(data):
    """Create WordPress block markup from extracted data"""
    
    # Clean up description for JSON
    description_clean = data['description'].replace('"', '\\"').replace('\n', '\\n')
    
    # Create the block attributes (only include non-empty values)
    attributes = {}
    if data['brandName']: attributes['brandName'] = data['brandName']
    if data['blendName']: attributes['blendName'] = data['blendName']
    if data['overallRating'] > 0: attributes['overallRating'] = data['overallRating']
    if data['totalReviews'] > 0: attributes['totalReviews'] = data['totalReviews']
    if data['star4Count'] > 0: attributes['star4Count'] = data['star4Count']
    if data['star3Count'] > 0: attributes['star3Count'] = data['star3Count']
    if data['star2Count'] > 0: attributes['star2Count'] = data['star2Count']
    if data['star1Count'] > 0: attributes['star1Count'] = data['star1Count']
    if data['series']: attributes['series'] = data['series']
    if data['blendedBy']: attributes['blendedBy'] = data['blendedBy']
    if data['manufacturedBy']: attributes['manufacturedBy'] = data['manufacturedBy']
    if data['blendType']: attributes['blendType'] = data['blendType']
    if data['contents']: attributes['contents'] = data['contents']
    if data['flavoring']: attributes['flavoring'] = data['flavoring']
    if data['cut']: attributes['cut'] = data['cut']
    if data['country']: attributes['country'] = data['country']
    if data['productionStatus']: attributes['productionStatus'] = data['productionStatus']
    if data['strength']: attributes['strength'] = data['strength']
    if data['flavoringRating']: attributes['flavoringRating'] = data['flavoringRating']
    if data['roomNote']: attributes['roomNote'] = data['roomNote']
    if data['taste']: attributes['taste'] = data['taste']
    if data['description']: attributes['description'] = description_clean
    if data['notes']: attributes['notes'] = data['notes']
    if data['imageUrl']: attributes['imageUrl'] = data['imageUrl']
    
    # Create the block markup
    attributes_json = json.dumps(attributes, separators=(',', ':'))
    
    # Build the rendered HTML
    html_parts = []
    
    # Header
    html_parts.append(f'<div class="wp-block-tobacco-archive-tobacco-blend"><div class="tobacco-blend-block"><div class="tobacco-blend-content"><div class="tobacco-blend-header"><h1 class="tobacco-blend-title"><span class="brand-name">{data["brandName"]}</span><span class="blend-name">{data["blendName"]}</span></h1>')
    
    # Rating (only if there are reviews)
    if data['totalReviews'] > 0:
        html_parts.append(f'<div class="tobacco-blend-rating">Rating: {data["overallRating"]}/4<span> from {data["totalReviews"]} reviews</span></div>')
    
    html_parts.append('</div><div class="tobacco-blend-main"><div class="tobacco-blend-info"><div class="tobacco-blend-details">')
    
    # Details
    if data['series']:
        html_parts.append(f'<p><strong>Series:</strong> {data["series"]}</p>')
    if data['blendedBy']:
        html_parts.append(f'<p><strong>Blended By:</strong> {data["blendedBy"]}</p>')
    if data['manufacturedBy']:
        html_parts.append(f'<p><strong>Manufactured By:</strong> {data["manufacturedBy"]}</p>')
    if data['blendType']:
        html_parts.append(f'<p><strong>Blend Type:</strong> {data["blendType"]}</p>')
    if data['contents']:
        html_parts.append(f'<p><strong>Contents:</strong> {data["contents"]}</p>')
    if data['flavoring']:
        html_parts.append(f'<p><strong>Flavoring:</strong> {data["flavoring"]}</p>')
    if data['cut']:
        html_parts.append(f'<p><strong>Cut:</strong> {data["cut"]}</p>')
    if data['country']:
        html_parts.append(f'<p><strong>Country:</strong> {data["country"]}</p>')
    if data['productionStatus']:
        html_parts.append(f'<p><strong>Production:</strong> {data["productionStatus"]}</p>')
    
    # Image (if available)
    if data['imageUrl']:
        html_parts.append(f'</div><div class="tobacco-blend-image"><img src="{data["imageUrl"]}" alt="{data["brandName"]} {data["blendName"]}"/></div>')
    else:
        html_parts.append('</div>')
    
    html_parts.append('</div>')
    
    # Profile ratings (only if any exist)
    has_profile = any([data['strength'], data['flavoringRating'], data['roomNote'], data['taste']])
    if has_profile:
        html_parts.append('<div class="tobacco-blend-profile"><h3>Profile</h3><div class="profile-ratings">')
        if data['strength']:
            html_parts.append(f'<div class="profile-rating"><strong>Strength:</strong> {data["strength"]}</div>')
        if data['flavoringRating']:
            html_parts.append(f'<div class="profile-rating"><strong>Flavoring:</strong> {data["flavoringRating"]}</div>')
        if data['roomNote']:
            html_parts.append(f'<div class="profile-rating"><strong>Room Note:</strong> {data["roomNote"]}</div>')
        if data['taste']:
            html_parts.append(f'<div class="profile-rating"><strong>Taste:</strong> {data["taste"]}</div>')
        html_parts.append('</div></div>')
    
    # Rating breakdown (only if there are reviews)
    if data['totalReviews'] > 0 and any([data['star4Count'], data['star3Count'], data['star2Count'], data['star1Count']]):
        html_parts.append('<div class="tobacco-blend-rating-breakdown"><h3>Rating Breakdown</h3><div class="rating-breakdown">')
        
        star_counts = [data['star4Count'], data['star3Count'], data['star2Count'], data['star1Count']]
        for i, count in enumerate(star_counts):
            stars = 4 - i
            if count > 0:
                percentage = (count / data['totalReviews']) * 100
                html_parts.append(f'<div class="rating-bar"><div class="rating-label">{stars} {"star" if stars == 1 else "stars"}:</div><div class="rating-visual"><div class="rating-bar-bg"><div class="rating-bar-fill" style="width:{percentage}%"></div></div><span class="rating-count">{count}</span></div></div>')
        
        html_parts.append('</div></div>')
    
    # Description
    if data['description']:
        html_parts.append(f'<div class="tobacco-blend-description"><h3>Description</h3><p>{data["description"]}</p></div>')
    
    # Notes
    if data['notes']:
        html_parts.append(f'<div class="tobacco-blend-notes"><h3>Notes</h3><p><strong>Notes:</strong> {data["notes"]}</p></div>')
    
    # Close all divs
    html_parts.append('</div></div></div></div>')
    
    # Combine everything
    rendered_html = ''.join(html_parts)
    
    markup = f"""<!-- wp:tobacco-archive/tobacco-blend {attributes_json} -->
{rendered_html}
<!-- /wp:tobacco-archive/tobacco-blend -->

<p><!-- Original URL: {data['original_url']} --></p>
"""
    
    return markup

def copy_and_rename_image(image_url, blend_name, source_dir):
    """Copy and rename image file"""
    if not image_url:
        return ""
    
    # Extract filename from URL
    filename = os.path.basename(urlparse(image_url).path)
    if not filename:
        return ""
    
    # Create safe filename from blend name
    safe_name = re.sub(r'[^\w\s-]', '', blend_name)
    safe_name = re.sub(r'[-\s]+', '-', safe_name).lower()
    new_filename = f"{safe_name}.jpg"
    
    # Find source image file
    source_file = None
    for root, dirs, files in os.walk(source_dir):
        if filename in files:
            source_file = os.path.join(root, filename)
            break
    
    if source_file and os.path.exists(source_file):
        dest_file = f"/media/glynn/2024/devilbox_public/glynnquelch2025/htdocs/wp-content/TR/compiled/images/{new_filename}"
        try:
            shutil.copy2(source_file, dest_file)
            return f"https://glynnquelch2025.gq/wp-content/TR/compiled/images/{new_filename}"
        except Exception as e:
            print(f"Error copying image for {blend_name}: {e}")
            return ""
    
    return ""

def main():
    """Main processing function"""
    tr_dir = "/media/glynn/2024/devilbox_public/glynnquelch2025/htdocs/wp-content/TR"
    compiled_dir = f"{tr_dir}/compiled"
    
    # Get all HTML files
    html_files = [f for f in os.listdir(tr_dir) if f.endswith('.html') and 'Tobacco Reviews' in f]
    
    print(f"Found {len(html_files)} tobacco review files")
    
    for html_file in sorted(html_files):
        print(f"Processing: {html_file}")
        
        try:
            # Extract data
            data = extract_blend_data(os.path.join(tr_dir, html_file))
            
            # Skip if data is None (blended by Gauntleys only)
            if data is None:
                print(f"  ⏭ Skipped: {html_file} (blended by Gauntleys only)")
                continue
            
            # Copy and rename image
            new_image_url = copy_and_rename_image(data['imageUrl'], data['blendName'], tr_dir)
            if new_image_url:
                data['imageUrl'] = new_image_url
            
            # Create markup
            markup = create_block_markup(data)
            
            # Save to compiled directory
            safe_name = re.sub(r'[^\w\s-]', '', data['blendName'])
            safe_name = re.sub(r'[-\s]+', '-', safe_name).lower()
            output_file = f"{compiled_dir}/{safe_name}.html"
            
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(markup)
            
            print(f"  ✓ Created: {output_file}")
            
        except Exception as e:
            print(f"  ✗ Error processing {html_file}: {e}")
    
    print(f"\nProcessing complete! Check the {compiled_dir} directory for results.")

if __name__ == "__main__":
    main()
