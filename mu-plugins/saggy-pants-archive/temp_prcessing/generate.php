<?php
/**
 * Playlist Generator - Single script that does everything
 * 
 * Usage: php generate.php <html-file>
 * 
 * Outputs:
 *   - playlist.csv (all tracks)
 *   - players/ folder with individual player blocks per artist
 */

require_once __DIR__ . '/PlaylistGenerator.php';

if ($argc < 2) {
    echo "Usage: php generate.php <html-file>\n";
    exit(1);
}

$htmlFile = $argv[1];

// Process
$generator = new PlaylistGenerator();
$generator->loadFile($htmlFile)->extract();

$trackCount = $generator->count();
$artists = $generator->getArtists();
$albums = $generator->getAlbums();

echo "=== Saggy Pants Audio Archive Generator ===\n\n";
echo "Extracted: {$trackCount} tracks\n";
echo "Artists: " . count($artists) . "\n";
echo "Compilations: " . count($albums) . "\n\n";

// 1. Export CSV
$csvFile = __DIR__ . '/playlist.csv';
$generator->toCsv($csvFile);
echo "✓ CSV saved: playlist.csv\n";

// 2. Create players directory
$playersDir = __DIR__ . '/players';
if (!is_dir($playersDir)) {
    mkdir($playersDir, 0755, true);
}

// 3. Generate player blocks for each artist
echo "\n--- Generating Player Blocks ---\n\n";

foreach ($artists as $artist) {
    $tracks = $generator->getTracksByArtist($artist);
    $trackCount = count($tracks);
    
    // Generate player block
    $playerHtml = $generator->toPlayerBlockByArtist($artist);
    
    // Save to file (sanitize filename)
    $filename = preg_replace('/[^a-z0-9]+/i', '-', strtolower($artist));
    $filename = trim($filename, '-') . '.html';
    file_put_contents("{$playersDir}/{$filename}", $playerHtml);
    
    echo "✓ {$artist} ({$trackCount} tracks) -> players/{$filename}\n";
}

// 4. Generate player blocks for compilations
echo "\n--- Compilations ---\n\n";

foreach ($albums as $album) {
    $playerHtml = $generator->toPlayerBlockByAlbum($album);
    
    $filename = preg_replace('/[^a-z0-9]+/i', '-', strtolower($album));
    $filename = trim($filename, '-') . '.html';
    file_put_contents("{$playersDir}/{$filename}", $playerHtml);
    
    echo "✓ {$album} -> players/{$filename}\n";
}

echo "\n=== Done! ===\n";
echo "CSV: playlist.csv\n";
echo "Players: players/*.html\n";
