#!/bin/bash

# Script to fix wp:gallery blocks in temp-content.html
# Ensures all galleries have: imageCrop:false, linkTo:"none", sizeSlug:"full"

INPUT_FILE="temp-content.html"
TEMP_FILE="temp-content.html.tmp"

# Check if file exists
if [ ! -f "$INPUT_FILE" ]; then
    echo "Error: $INPUT_FILE not found!"
    exit 1
fi

# Create backup
cp "$INPUT_FILE" "${INPUT_FILE}.backup"

# Process the file
# Replace wp:gallery blocks to ensure they have the correct attributes
# This handles various formats:
# 1. <!-- wp:gallery -->
# 2. <!-- wp:gallery {"imageCrop":false} -->
# 3. <!-- wp:gallery {"imageCrop":false,"linkTo":"none"} -->
# etc.

# Use perl for more complex regex replacement
perl -i -pe '
    # Match wp:gallery blocks
    if (/<!-- wp:gallery/) {
        # If it already has attributes, update them
        if (/\{/) {
            # Remove existing imageCrop, linkTo, sizeSlug if present
            s/"imageCrop":[^,}]+,?//g;
            s/,"imageCrop":[^,}]+//g;
            s/"linkTo":[^,}]+,?//g;
            s/,"linkTo":[^,}]+//g;
            s/"sizeSlug":[^,}]+,?//g;
            s/,"sizeSlug":[^,}]+//g;
            
            # Clean up any double commas or trailing commas before }
            s/,\s*,/,/g;
            s/,\s*}/}/g;
            s/\{\s*,/\{/g;
            
            # Add the required attributes
            # Check if we have an empty object or need to add comma
            if (/\{\s*\}/) {
                s/\{\s*\}/{"imageCrop":false,"linkTo":"none","sizeSlug":"full"}/;
            } else {
                # Add attributes with proper comma
                s/\{/\{"imageCrop":false,"linkTo":"none","sizeSlug":"full",/;
            }
        } else {
            # No attributes, add them
            s/<!-- wp:gallery/<!-- wp:gallery {"imageCrop":false,"linkTo":"none","sizeSlug":"full"}/;
        }
    }
' "$INPUT_FILE"

# Also ensure all wp:image blocks have lightbox enabled
perl -i -pe '
    # Match wp:image blocks
    if (/<!-- wp:image/) {
        # If it has attributes, ensure lightbox is enabled
        if (/\{/) {
            # Check if lightbox exists
            if (!/"lightbox"/) {
                # Add lightbox before closing brace
                s/\{/\{"lightbox":{"enabled":true},/;
            } else {
                # Ensure lightbox is enabled
                s/"lightbox":\{[^}]*\}/"lightbox":{"enabled":true}/g;
            }
        } else {
            # No attributes, add lightbox
            s/<!-- wp:image/<!-- wp:image {"lightbox":{"enabled":true}/;
        }
    }
' "$INPUT_FILE"

echo "Gallery and image blocks updated in $INPUT_FILE"
echo "Backup saved as ${INPUT_FILE}.backup"


