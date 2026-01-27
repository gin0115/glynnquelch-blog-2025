#!/usr/bin/env bash
set -euo pipefail

# Requires: ImageMagick (magick)

# Source directory - each file is 1 page (no splitting)
source_dir="/media/glynn/External/torrent/Saggy-Mag/01a-Sept 05/"

desktop_dir="desktop"
web_dir="web"
mkdir -p "$desktop_dir" "$web_dir"

# Check if source directory exists
if [ ! -d "$source_dir" ]; then
  echo "Error: Source directory not found: $source_dir"
  exit 1
fi

echo "Processing files from: $source_dir"

# Find PSDs in source directory (not recursive), robust with spaces/special chars
find "$source_dir" -maxdepth 1 -type f \( -iname '*.psd' \) -print0 |
while IFS= read -r -d '' psd; do
  base="${psd##*/}"
  stem="${base%.*}"

  # Desktop: Full-size uncompressed PNG (no splitting - each file is 1 page)
  desktop_output="${desktop_dir}/${stem}.png"
  
  # Web: Resized monochrome WebP (1200-1600px long edge, q80)
  web_output="${web_dir}/${stem}.webp"

  echo "Processing: $base"

  # 1) Create full-size uncompressed PNG for desktop (no crop, no split)
  convert "${psd}[0]" -auto-orient -colorspace sRGB -flatten \
    -define png:compression-level=0 \
    "${desktop_output}"

  # 2) Create WebP version for web: resize to 1200-1600px long edge, monochrome, q80
  # Calculate resize dimensions (long edge between 1200-1600px, maintain aspect ratio)
  # Use 1400px as target (middle of range)
  convert "${desktop_output}" \
    -resize 1400x1400\> \
    -colorspace Gray \
    -define webp:quality=80 \
    "${web_output}"
done

echo "Processing complete!"


