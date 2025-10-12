#!/usr/bin/env python3
"""
Script to check which tobacco files have profile ratings that need manual correction
"""

import os
import json
import re

def check_profile_ratings(html_file):
    """Check if profile ratings need manual correction"""
    with open(html_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Extract blend name from title
    title_match = re.search(r'<title>Tobacco Reviews \| ([^-]+) - ([^<]+)</title>', content)
    if title_match:
        brand_name = title_match.group(1).strip()
        blend_name = title_match.group(2).strip()
    else:
        return None
    
    # Check profile ratings
    strength_match = re.search(r'<div class="row divProfileRatingName"> <strong>Strength</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    strength = strength_match.group(1).strip() if strength_match else ""
    
    flavoring_match = re.search(r'<div class="row divProfileRatingName"> <strong>Flavoring</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    flavoring = flavoring_match.group(1).strip() if flavoring_match else ""
    
    room_note_match = re.search(r'<div class="row divProfileRatingName"> <strong>Room Note</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    room_note = room_note_match.group(1).strip() if room_note_match else ""
    
    taste_match = re.search(r'<div class="row divProfileRatingName"> <strong>Taste</strong></div>\s*<div class="row divProfileRatingHigh">([^<]+)</div>', content)
    taste = taste_match.group(1).strip() if taste_match else ""
    
    # Check if any are "0" or empty
    needs_correction = any(rating == "0" or rating == "" for rating in [strength, flavoring, room_note, taste])
    
    return {
        'file': html_file,
        'brand': brand_name,
        'blend': blend_name,
        'strength': strength,
        'flavoring': flavoring,
        'room_note': room_note,
        'taste': taste,
        'needs_correction': needs_correction
    }

def main():
    """Main function"""
    tr_dir = "/media/glynn/2024/devilbox_public/glynnquelch2025/htdocs/wp-content/TR"
    
    # Get all HTML files
    html_files = [f for f in os.listdir(tr_dir) if f.endswith('.html') and 'Tobacco Reviews' in f]
    
    print("Checking profile ratings...")
    print("=" * 80)
    
    needs_correction = []
    all_good = []
    
    for html_file in sorted(html_files):
        result = check_profile_ratings(os.path.join(tr_dir, html_file))
        if result:
            if result['needs_correction']:
                needs_correction.append(result)
                print(f"❌ {result['blend']} - Needs correction")
                print(f"   Strength: '{result['strength']}', Flavoring: '{result['flavoring']}', Room Note: '{result['room_note']}', Taste: '{result['taste']}'")
            else:
                all_good.append(result)
                print(f"✅ {result['blend']} - OK")
    
    print("\n" + "=" * 80)
    print(f"Summary:")
    print(f"✅ {len(all_good)} files have good profile ratings")
    print(f"❌ {len(needs_correction)} files need manual correction")
    
    if needs_correction:
        print(f"\nFiles that need correction:")
        for item in needs_correction:
            print(f"  - {item['blend']}")

if __name__ == "__main__":
    main()
