# Tobacco Reviews Import

This WP-CLI command imports tobacco reviews from HTML files into WordPress posts.

## Setup

1. Place your HTML files and images in the `import/` directory:
   ```
   import/
   ├── *.html files (tobacco reviews)
   └── images/
       └── *.jpg files (tobacco images)
   ```

2. The HTML files should contain:
   - WordPress block markup with tobacco blend attributes
   - Original URL in a comment
   - Full rendered HTML content

## Usage

### Basic Import
```bash
wp tobacco-import import-reviews
```

### Dry Run (see what would be imported)
```bash
wp tobacco-import import-reviews --dry-run
```

### Force Reimport (overwrite existing posts)
```bash
wp tobacco-import import-reviews --force
```

## What it does

1. **Reads HTML files** from the `import/` directory
2. **Extracts block attributes** from the WordPress block markup
3. **Creates/updates posts** of type `tobacco_review`
4. **Uploads images** to `/wp-content/uploads/tobacco-reviews/`
5. **Sets featured images** for each post
6. **Updates image URLs** in the block content to use the new uploaded images
7. **Stores original URL** as post meta

## Post Structure

Each imported post will have:
- **Post Title**: Brand Name + Blend Name
- **Post Content**: Full HTML with block markup and rendered content
- **Featured Image**: The tobacco image
- **Post Meta**: `_original_url` with the original TobaccoReviews.com URL
- **Post Type**: `tobacco_review` (not publicly accessible)

## Image Handling

- Images are copied from `import/images/` to `/wp-content/uploads/tobacco-reviews/`
- Images are uploaded as WordPress attachments
- Featured image is set for each post
- Image URLs in the block content are updated to use the new WordPress URLs

## Error Handling

The command will:
- Skip posts that already exist (unless `--force` is used)
- Show warnings for missing images
- Report import statistics at the end
- Continue processing even if individual files fail

## Example Output

```
Found 45 HTML files to process
Imported: GQ Tobaccos Askwith Kake
Imported: GQ Tobaccos Balkan Full No 1
Skipped: GQ Tobaccos Bayou Blend - Post already exists
...

Import complete!
Imported: 42
Skipped: 3
Errors: 0
```
