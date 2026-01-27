<?php
/**
 * Simple debug script to extract content from #Layer1 > table > tr > td > table > tr > td
 * and log to temp file
 */

/**
 * Concatenate lines that end with <br> where the character before is not a space
 * Also handle lines split within <p align="left"> tags (only if they're actual word splits)
 */
function concatenateBrLines($text) {
    // Split by newlines to get actual lines
    $lines = preg_split('/\r?\n/', $text);
    $result = [];
    
    for ($i = 0; $i < count($lines); $i++) {
        $current = $lines[$i];
        $trimmedCurrent = trim($current);
        
        // First, handle lines split within <p align="left"> tags
        // Only join if current line is clearly incomplete (doesn't end with <br>, &gt;, or tag)
        // and next line is indented text continuation
        if ($trimmedCurrent !== '' && 
            !preg_match('/(<br\s*\/?>|&gt;|<\/?[a-z])$/i', $trimmedCurrent) &&
            isset($lines[$i + 1])) {
            
            $nextLine = $lines[$i + 1];
            $trimmedNext = trim($nextLine);
            
            // Only join if:
            // 1. Next line is indented (starts with spaces/tabs)
            // 2. Next line doesn't start with &gt;, <, or <br> (not a new entry)
            // 3. Current line doesn't end with punctuation (complete sentence)
            // 4. Next line is actual text content (not just whitespace or tags)
            if (preg_match('/^\s+/', $nextLine) && 
                $trimmedNext !== '' && 
                !preg_match('/^(&gt;|<br|<[a-z])/i', $trimmedNext) &&
                !preg_match('/[.!?]$/', rtrim($trimmedCurrent)) &&
                !preg_match('/^[\s<]*$/', $trimmedNext)) {
                
                // Join them: trim current, add space, trim next
                $result[] = $trimmedCurrent . ' ' . $trimmedNext;
                $i++; // Skip the next line since we've concatenated it
                continue;
            }
        }
        
        // Check if line ends with <br> (case insensitive, optional attributes)
        if (preg_match('/(.*?)(<br\s*\/?>)$/i', $current, $matches)) {
            $content = $matches[1];
            $brTag = $matches[2];
            
            // Get the last non-whitespace character before <br>
            $trimmed = rtrim($content);
            $lastChar = $trimmed !== '' ? substr($trimmed, -1) : '';
            
            // If last char is not a space and next line exists
            if ($lastChar !== ' ' && $lastChar !== '' && isset($lines[$i + 1])) {
                $nextLine = trim($lines[$i + 1]);
                
                // Only concatenate if next line doesn't start with <br>, &gt;, or is empty
                if ($nextLine !== '' && 
                    !preg_match('/^(<br|&gt;)/i', $nextLine) &&
                    !preg_match('/^[\s<]*$/', $nextLine)) {
                    // Concatenate: current content + <br> + space + trimmed next line
                    $result[] = $content . $brTag . ' ' . $nextLine;
                    $i++; // Skip the next line since we've concatenated it
                    continue;
                }
            }
        }
        
        $result[] = $current;
    }
    
    return implode("\n", $result);
}

$gid_dir = __DIR__ . '/gid';
$log_file = __DIR__ . '/extract-debug.log';

// Clear log file
file_put_contents($log_file, "=== GIG EXTRACTION DEBUG ===\n\n");

$files = glob($gid_dir . '/*.htm');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
    $filename = basename($file);
    $day = (int) basename($file, '.htm');
    
    $html = file_get_contents($file);
    
    // Convert encoding
    if (!mb_check_encoding($html, 'UTF-8')) {
        $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
    }
    
    // Try to extract using DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    $xpath = new DOMXPath($dom);
    
    // Find #Layer1 > table > tr > td > table > tr > td
    // The inner td with bgcolor="4b5672" contains the gig info
    $nodes = $xpath->query("//div[@id='Layer1']//table//tr//td//table//tr//td[@bgcolor='4b5672']");
    
    if ($nodes->length > 0) {
        $tdNode = $nodes->item(0);
        $content = $dom->saveHTML($tdNode);
        
        // Check if it says "There are no gigs"
        $hasNoGigs = (stripos($content, 'There are no gigs') !== false);
        $flag = $hasNoGigs ? ' [NO GIGS]' : '';
        
        file_put_contents($log_file, "=== FILE: $filename (Day $day)$flag ===\n", FILE_APPEND);
        
        // If there are gigs, extract the 2nd <p> tag
        if (!$hasNoGigs) {
            $pTags = $xpath->query(".//p", $tdNode);
            if ($pTags->length >= 2) {
                $secondP = $dom->saveHTML($pTags->item(1));
                
                // Strip all <font> and </font> tags first
                $secondP = preg_replace('/<\/?font[^>]*>/i', '', $secondP);
                
                // Concatenate lines: if line ends with <br> and char before is not space, concat next line
                $secondP = concatenateBrLines($secondP);
                
                // Remove the footer text - do this AFTER concatenation to catch all variations
                $secondP = preg_replace('/&gt;If you have a gig, on this date\.?\s*Let us know.*?gigs@saggy-pants\.com.*?<\/a>\.?/is', '', $secondP);
                $secondP = preg_replace('/If you have a gig, on this date\.?\s*Let us know.*?gigs@saggy-pants\.com.*?<\/a>\.?/is', '', $secondP);
                // Also remove any trailing period/whitespace that might be left
                $secondP = preg_replace('/\s*&gt;If you have a gig.*?$/is', '', $secondP);
                // Remove any remaining footer fragments
                $secondP = preg_replace('/<br>\s*&gt;If you have a gig.*?$/is', '', $secondP);
                
                file_put_contents($log_file, "--- 2nd P TAG (CLEANED) ---\n", FILE_APPEND);
                file_put_contents($log_file, $secondP . "\n", FILE_APPEND);
            } else {
                file_put_contents($log_file, "--- WARNING: Less than 2 P tags found ---\n", FILE_APPEND);
                file_put_contents($log_file, $content . "\n", FILE_APPEND);
            }
        } else {
            // Show full content for no-gigs files
            file_put_contents($log_file, $content . "\n", FILE_APPEND);
        }
        
        file_put_contents($log_file, "\n" . str_repeat("-", 80) . "\n\n", FILE_APPEND);
    } else {
        // Fallback: try regex
        if (preg_match('/<td[^>]*bgcolor=["\']?4b5672[^>]*>(.*?)<\/td>/is', $html, $matches)) {
            $content = $matches[1];
            
            // Check if it says "There are no gigs"
            $hasNoGigs = (stripos($content, 'There are no gigs') !== false);
            $flag = $hasNoGigs ? ' [NO GIGS]' : '';
            
            file_put_contents($log_file, "=== FILE: $filename (Day $day)$flag - REGEX FALLBACK ===\n", FILE_APPEND);
            
            // If there are gigs, extract the 2nd <p> tag using regex
            if (!$hasNoGigs) {
                if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $content, $pMatches)) {
                    if (count($pMatches[0]) >= 2) {
                        $secondP = $pMatches[0][1];
                        
                        // Strip all <font> and </font> tags first
                        $secondP = preg_replace('/<\/?font[^>]*>/i', '', $secondP);
                        
                        // Concatenate lines: if line ends with <br> and char before is not space, concat next line
                        $secondP = concatenateBrLines($secondP);
                        
                        // Remove the footer text - do this AFTER concatenation to catch all variations
                        $secondP = preg_replace('/&gt;If you have a gig, on this date\.?\s*Let us know.*?gigs@saggy-pants\.com.*?<\/a>\.?/is', '', $secondP);
                        $secondP = preg_replace('/If you have a gig, on this date\.?\s*Let us know.*?gigs@saggy-pants\.com.*?<\/a>\.?/is', '', $secondP);
                        // Also remove any trailing period/whitespace that might be left
                        $secondP = preg_replace('/\s*&gt;If you have a gig.*?$/is', '', $secondP);
                        // Remove any remaining footer fragments
                        $secondP = preg_replace('/<br>\s*&gt;If you have a gig.*?$/is', '', $secondP);
                        
                        file_put_contents($log_file, "--- 2nd P TAG (CLEANED) ---\n", FILE_APPEND);
                        file_put_contents($log_file, '<p>' . $secondP . '</p>' . "\n", FILE_APPEND);
                    } else {
                        file_put_contents($log_file, "--- WARNING: Less than 2 P tags found ---\n", FILE_APPEND);
                        file_put_contents($log_file, $content . "\n", FILE_APPEND);
                    }
                } else {
                    file_put_contents($log_file, "--- WARNING: No P tags found ---\n", FILE_APPEND);
                    file_put_contents($log_file, $content . "\n", FILE_APPEND);
                }
            } else {
                // Show full content for no-gigs files
                file_put_contents($log_file, $content . "\n", FILE_APPEND);
            }
            
            file_put_contents($log_file, "\n" . str_repeat("-", 80) . "\n\n", FILE_APPEND);
        } else {
            file_put_contents($log_file, "=== FILE: $filename (Day $day) - NOT FOUND ===\n\n", FILE_APPEND);
        }
    }
}

echo "Extraction complete! Check: $log_file\n";

