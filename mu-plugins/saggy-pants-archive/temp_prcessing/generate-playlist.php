<?php
/**
 * Playlist Generator - Parses audio archive HTML and generates markdown TABLE
 * 
 * Usage: php generate-playlist.php temp-content.html > playlist.md
 */

if ($argc < 2) {
    echo "Usage: php generate-playlist.php <html-file>\n";
    exit(1);
}

$htmlFile = $argv[1];
if (!file_exists($htmlFile)) {
    echo "Error: File not found: $htmlFile\n";
    exit(1);
}

$html = file_get_contents($htmlFile);

// Create DOM parser
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

/**
 * Clean up track/artist names
 */
function cleanName($name) {
    // Replace underscores with spaces
    $name = str_replace('_', ' ', $name);
    // Remove trailing dash (with or without spaces) - regular dash, en-dash, em-dash
    $name = preg_replace('/\s*[\x{2013}\x{2014}-]\s*$/u', '', $name);
    // Trim whitespace
    return trim($name);
}

/**
 * Capitalize track name (title case)
 */
function capitalizeTrack($name) {
    return ucwords(strtolower($name));
}

/**
 * Artists whose track names should have dashes removed
 */
function shouldRemoveDashes($artist) {
    $noDashArtists = [
        'lucky bullet',
        'mawda',
        'moth',
        'swound',
        'the starrlings',
        'thisisthewaytheworldends',
    ];
    return in_array(strtolower($artist), $noDashArtists);
}

/**
 * Apply track name fixes
 */
function fixTrackName($track, $artist) {
    // Hardcoded fixes
    if (stripos($track, 'Axminster Scotchguard and the Numskulls-Yes Car Credit') !== false 
        || stripos($track, 'Axminster Scotchguard and the Numskulls Yes Car Credit') !== false) {
        $track = 'Yes Car Credit';
    }
    
    // Remove dashes for specific artists
    if (shouldRemoveDashes($artist)) {
        $track = str_replace('-', ' ', $track);
        $track = preg_replace('/\s+/', ' ', $track); // collapse multiple spaces
    }
    
    // Capitalize
    $track = capitalizeTrack($track);
    
    return trim($track);
}

/**
 * Parse compilation track format: "01 – Artist – Track"
 * Handles en-dash (–), em-dash (—), and regular dash (-)
 */
function parseCompilationTrack($trackTitle) {
    // Normalize all dash types to regular dash for easier parsing
    $trackTitle = preg_replace('/[\x{2013}\x{2014}]/u', '-', $trackTitle);
    
    // Remove leading track numbers like "01 -" or "01-"
    $trackTitle = preg_replace('/^\d+\s*-\s*/', '', $trackTitle);
    
    // Split on " - " to get artist and track (first occurrence only)
    $parts = explode(' - ', $trackTitle, 2);
    
    if (count($parts) === 2) {
        return [
            'artist' => cleanName($parts[0]),
            'track' => cleanName($parts[1]),
        ];
    }
    
    // Try splitting on just "-" if no space-dash-space found
    $parts = explode('-', $trackTitle, 2);
    if (count($parts) === 2) {
        return [
            'artist' => cleanName($parts[0]),
            'track' => cleanName($parts[1]),
        ];
    }
    
    // If no separator found, return as track only
    return [
        'artist' => 'Unknown',
        'track' => cleanName($trackTitle),
    ];
}

// Find all posts
$posts = $xpath->query("//li[contains(@class, 'wp-block-post')]");

$artistTracks = [];
$compilationTracks = [];

foreach ($posts as $post) {
    $classes = $post->getAttribute('class');
    
    // Extract post ID
    preg_match('/post-(\d+)/', $classes, $postIdMatch);
    $postId = $postIdMatch[1] ?? 'unknown';
    
    // Get title
    $titleNode = $xpath->query(".//h2[contains(@class, 'wp-block-post-title')]//a", $post)->item(0);
    $title = $titleNode ? trim($titleNode->textContent) : 'Unknown';
    
    // Detect compilation: has tag-compilation OR specific known compilations
    $isCompilation = strpos($classes, 'tag-compilation') !== false 
                  || $postId === '6041'  // Junktion 7 BOTB 2004 Compilation
                  || $postId === '4533'; // Saggy Pants Compilation II
    
    // Remove "(Audio)" from title for artist name
    $artist = preg_replace('/\s*\(Audio\)\s*$/i', '', $title);
    $artist = cleanName($artist);
    
    // Get cover image (first image in content, not in gallery for compilations)
    $coverImg = '';
    $imgNodes = $xpath->query(".//figure[contains(@class, 'wp-block-image') and not(ancestor::figure[contains(@class, 'wp-block-gallery')])]//img", $post);
    if ($imgNodes->length > 0) {
        $coverImg = $imgNodes->item(0)->getAttribute('src');
    }
    
    // For compilations, get gallery images
    $galleryImages = [];
    if ($isCompilation) {
        $galleryImgNodes = $xpath->query(".//figure[contains(@class, 'wp-block-gallery')]//img", $post);
        foreach ($galleryImgNodes as $gImg) {
            $galleryImages[] = $gImg->getAttribute('src');
        }
    }
    
    // Get tracks
    $contentNode = $xpath->query(".//div[contains(@class, 'wp-block-post-content')]", $post)->item(0);
    
    if ($contentNode) {
        $listItems = $xpath->query(".//ul[contains(@class, 'wp-block-list')]//li", $contentNode);
        $audioElements = $xpath->query(".//figure[contains(@class, 'wp-block-audio')]", $contentNode);
        
        $listIndex = 0;
        foreach ($audioElements as $index => $audioEl) {
            $audioSrc = $xpath->query(".//audio", $audioEl)->item(0);
            $mp3Url = $audioSrc ? $audioSrc->getAttribute('src') : '';
            
            // Get figcaption if exists
            $figcaption = $xpath->query(".//figcaption", $audioEl)->item(0);
            $caption = $figcaption ? cleanName($figcaption->textContent) : null;
            
            // Get corresponding list item title
            $listTitle = null;
            if ($listItems->item($listIndex)) {
                $listTitle = trim($listItems->item($listIndex)->textContent);
                $listIndex++;
            }
            
            $rawTrackTitle = $listTitle ?: $caption ?: basename($mp3Url, '.mp3');
            
            if ($isCompilation) {
                // Parse "01 – Artist – Track" format for compilations
                $parsed = parseCompilationTrack($rawTrackTitle);
                $trackName = fixTrackName($parsed['track'], $parsed['artist']);
                $track = [
                    'artist' => $parsed['artist'],
                    'track' => $trackName,
                    'album' => $artist, // The post title is the album
                    'mp3' => $mp3Url,
                    'cover' => !empty($galleryImages) ? $galleryImages[0] : $coverImg,
                    'gallery' => $galleryImages,
                    'post_id' => $postId,
                ];
                $compilationTracks[] = $track;
            } else {
                $trackTitle = cleanName($rawTrackTitle);
                
                // If caption differs from title, add it in brackets
                if ($caption && $listTitle && cleanName($caption) !== cleanName($listTitle)) {
                    $trackTitle = cleanName($listTitle) . ' [' . $caption . ']';
                }
                
                // Apply track name fixes (capitalize, remove dashes for specific artists)
                $trackTitle = fixTrackName($trackTitle, $artist);
                
                $track = [
                    'artist' => $artist,
                    'track' => $trackTitle,
                    'mp3' => $mp3Url,
                    'cover' => $coverImg,
                    'post_id' => $postId,
                ];
                $artistTracks[] = $track;
            }
        }
    }
}

// Merge all tracks into one list
$allTracks = array_merge($artistTracks, $compilationTracks);

// Sort all tracks alphabetically by artist, then track
usort($allTracks, function($a, $b) {
    $artistCmp = strcasecmp($a['artist'], $b['artist']);
    if ($artistCmp !== 0) return $artistCmp;
    return strcasecmp($a['track'], $b['track']);
});

// Generate Markdown TABLE output
echo "# Saggy Pants Audio Archive Playlist\n\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// One table for ALL tracks
$totalTracks = count($allTracks);
echo "## All Tracks ({$totalTracks} tracks)\n\n";
echo "| # | Artist | Track | Album | Cover | MP3 |\n";
echo "|---|--------|-------|-------|-------|-----|\n";

$num = 1;
foreach ($allTracks as $track) {
    $album = $track['album'] ?? '-';
    $coverCell = $track['cover'] ? "![cover]({$track['cover']})" : '-';
    $mp3File = str_replace('_', ' ', basename($track['mp3']));
    echo "| {$num} | {$track['artist']} | {$track['track']} | {$album} | {$coverCell} | [{$mp3File}]({$track['mp3']}) |\n";
    $num++;
}

echo "\n---\n\n";

// Summary
echo "## Summary\n\n";
echo "| Stat | Count |\n";
echo "|------|-------|\n";
echo "| Artists | " . count(array_unique(array_column($artistTracks, 'artist'))) . " |\n";
echo "| Artist Tracks | " . count($artistTracks) . " |\n";
echo "| Compilation Tracks | " . count($compilationTracks) . " |\n";
echo "| **Total Tracks** | " . (count($artistTracks) + count($compilationTracks)) . " |\n";
