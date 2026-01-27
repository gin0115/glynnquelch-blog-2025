<?php
/**
 * Playlist Generator
 * 
 * Takes HTML source, extracts audio tracks, outputs CSV and WordPress player blocks.
 * 
 * Usage:
 *   $generator = new PlaylistGenerator($htmlSource);
 *   $csv = $generator->getCsv();
 *   $blocks = $generator->getPlayerBlocks();
 */

class PlaylistGenerator {
    
    private array $tracks = [];
    private array $compilationPostIds = ['6041', '4533'];
    private array $noDashArtists = ['lucky bullet', 'mawda', 'moth', 'swound', 'the starrlings', 'thisisthewaytheworldends'];
    
    public function __construct(string $html) {
        $this->parse($html);
    }
    
    private function parse(string $html): void {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $posts = $xpath->query("//li[contains(@class, 'wp-block-post')]");
        
        foreach ($posts as $post) {
            $classes = $post->getAttribute('class');
            preg_match('/post-(\d+)/', $classes, $m);
            $postId = $m[1] ?? 'unknown';
            
            $titleNode = $xpath->query(".//h2[contains(@class, 'wp-block-post-title')]//a", $post)->item(0);
            $title = $titleNode ? trim($titleNode->textContent) : 'Unknown';
            $pageLink = $titleNode ? $titleNode->getAttribute('href') : '';
            
            $isCompilation = strpos($classes, 'tag-compilation') !== false || in_array($postId, $this->compilationPostIds);
            $artist = $this->clean(preg_replace('/\s*\(Audio\)\s*$/i', '', $title));
            $album = $isCompilation ? $artist : null;
            
            // Cover
            $cover = null;
            if ($isCompilation) {
                $img = $xpath->query(".//figure[contains(@class, 'wp-block-gallery')]//img", $post)->item(0);
            } else {
                $img = $xpath->query(".//figure[contains(@class, 'wp-block-image') and not(ancestor::figure[contains(@class, 'wp-block-gallery')])]//img", $post)->item(0);
            }
            if ($img) $cover = $img->getAttribute('src');
            
            // Tracks
            $content = $xpath->query(".//div[contains(@class, 'wp-block-post-content')]", $post)->item(0);
            if (!$content) continue;
            
            $listItems = $xpath->query(".//ul[contains(@class, 'wp-block-list')]//li", $content);
            $audioElements = $xpath->query(".//figure[contains(@class, 'wp-block-audio')]", $content);
            
            $listIndex = 0;
            foreach ($audioElements as $audioEl) {
                $audioSrc = $xpath->query(".//audio", $audioEl)->item(0);
                $mp3 = $audioSrc ? $audioSrc->getAttribute('src') : '';
                if (!$mp3) continue;
                
                $caption = null;
                $figcaption = $xpath->query(".//figcaption", $audioEl)->item(0);
                if ($figcaption) $caption = $this->clean($figcaption->textContent);
                
                $listTitle = null;
                if ($listItems->item($listIndex)) {
                    $listTitle = trim($listItems->item($listIndex)->textContent);
                    $listIndex++;
                }
                
                $raw = $listTitle ?: $caption ?: basename($mp3, '.mp3');
                
                if ($isCompilation) {
                    $parsed = $this->parseCompTrack($raw);
                    $trackArtist = $parsed[0];
                    $trackName = $this->fixTrack($parsed[1], $trackArtist);
                } else {
                    $trackArtist = $artist;
                    $trackName = $this->fixTrack($this->clean($raw), $artist);
                }
                
                $this->tracks[] = [
                    'artist' => $trackArtist,
                    'track' => $trackName,
                    'album' => $album,
                    'cover' => $cover,
                    'mp3' => $mp3,
                    'pageLink' => $pageLink,
                ];
            }
        }
        
        // Sort by artist, then track
        usort($this->tracks, fn($a, $b) => strcasecmp($a['artist'], $b['artist']) ?: strcasecmp($a['track'], $b['track']));
    }
    
    private function clean(string $s): string {
        $s = str_replace('_', ' ', $s);
        $s = preg_replace('/\s*[\x{2013}\x{2014}-]\s*$/u', '', $s);
        return trim($s);
    }
    
    private function parseCompTrack(string $t): array {
        $t = preg_replace('/[\x{2013}\x{2014}]/u', '-', $t);
        $t = preg_replace('/^\d+\s*-\s*/', '', $t);
        $parts = explode(' - ', $t, 2);
        if (count($parts) === 2) return [$this->clean($parts[0]), $this->clean($parts[1])];
        $parts = explode('-', $t, 2);
        if (count($parts) === 2) return [$this->clean($parts[0]), $this->clean($parts[1])];
        return ['Unknown', $this->clean($t)];
    }
    
    private function fixTrack(string $t, string $artist): string {
        if (stripos($t, 'Axminster') !== false && stripos($t, 'Yes Car Credit') !== false) $t = 'Yes Car Credit';
        if (in_array(strtolower($artist), $this->noDashArtists)) $t = preg_replace('/\s+/', ' ', str_replace('-', ' ', $t));
        return ucwords(strtolower(trim($t)));
    }
    
    public function getTracks(): array {
        return $this->tracks;
    }
    
    public function getArtists(): array {
        return array_values(array_unique(array_column($this->tracks, 'artist')));
    }
    
    public function getCsv(): string {
        $lines = ["Artist,Track,Album,Cover,MP3"];
        foreach ($this->tracks as $t) {
            $lines[] = sprintf('"%s","%s","%s","%s","%s"',
                str_replace('"', '""', $t['artist']),
                str_replace('"', '""', $t['track']),
                str_replace('"', '""', $t['album'] ?? ''),
                $t['cover'] ?? '',
                $t['mp3']
            );
        }
        return implode("\n", $lines);
    }
    
    public function getPlayerBlock(string $artist): string {
        $tracks = array_filter($this->tracks, fn($t) => strcasecmp($t['artist'], $artist) === 0);
        return $this->buildBlock(array_values($tracks));
    }
    
    /**
     * Get ONE player block with ALL tracks - the full jukebox
     */
    public function getFullPlaylist(): string {
        return $this->buildBlock($this->tracks);
    }
    
    private function buildBlock(array $tracks): string {
        $blockId = 'jukebox-' . substr(md5(uniqid()), 0, 8);
        $trackList = [];
        foreach ($tracks as $i => $t) {
            $trackList[] = [
                'id' => 'track-' . ($i + 1),
                'title' => $t['track'],
                'artist' => $t['artist'],
                'album' => $t['album'] ?? '',
                'cover' => $t['cover'] ?? '',
                'url' => $t['mp3'],
                'pageLink' => $t['pageLink'] ?? '',
            ];
        }
        $json = json_encode(['blockId' => $blockId, 'tracks' => $trackList], JSON_UNESCAPED_SLASHES);
        $style = "<style>.jukebox__track-cover img { margin-top: 0 !important; margin-bottom: 0 !important; }</style>\n";
        return $style . "<!-- wp:pinkcrab/jukebox {$json} /-->";
    }
}

// === RUN IF CALLED DIRECTLY ===
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $html = file_get_contents($argv[1]);
    $gen = new PlaylistGenerator($html);
    
    echo "=== Extracted " . count($gen->getTracks()) . " tracks ===\n\n";
    
    // Output CSV
    file_put_contents(__DIR__ . '/playlist.csv', $gen->getCsv());
    echo "✓ Saved: playlist.csv\n\n";
    
    // Output player blocks
    $blocksDir = __DIR__ . '/players';
    if (!is_dir($blocksDir)) mkdir($blocksDir, 0755, true);
    
    foreach ($gen->getPlayerBlocks() as $artist => $block) {
        $file = preg_replace('/[^a-z0-9]+/i', '-', strtolower($artist)) . '.html';
        file_put_contents("{$blocksDir}/{$file}", $block);
        echo "✓ {$artist} -> players/{$file}\n";
    }
    
    echo "\nDone!\n";
}
