<?php
/**
 * Extract Gig Guide Data from HTML Files
 * 
 * Scans all .htm files in gid/2005/{month}/ directories and extracts gig information
 */

// Configuration
$gid_base_dir = __DIR__ . '/gid/2005';
$output_file = __DIR__ . '/gigs-extracted.json';

// Month name to number mapping
$month_map = [
    'jan' => 1, 'january' => 1,
    'feb' => 2, 'february' => 2,
    'mar' => 3, 'march' => 3,
    'apr' => 4, 'april' => 4,
    'may' => 5,
    'june' => 6, 'jun' => 6,
    'july' => 7, 'jul' => 7,
    'aug' => 8, 'august' => 8,
    'sept' => 9, 'sep' => 9, 'september' => 9,
    'oct' => 10, 'october' => 10,
    'nov' => 11, 'november' => 11,
    'dec' => 12, 'december' => 12,
];

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



/**
 * Parse HTML file and extract gig information
 */
function parseGigFile($filepath, $day, $year, $month) {
    $html = file_get_contents($filepath);
    
    // Convert encoding
    if (!mb_check_encoding($html, 'UTF-8')) {
        $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
    }
    
    // Use DOMDocument to extract content
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    $xpath = new DOMXPath($dom);
    
    // Find #Layer1 > table > tr > td > table > tr > td with bgcolor="4b5672"
    $nodes = $xpath->query("//div[@id='Layer1']//table//tr//td//table//tr//td[@bgcolor='4b5672']");
    
    if ($nodes->length === 0) {
        // Fallback: try regex
        if (!preg_match('/<td[^>]*bgcolor=["\']?4b5672[^>]*>(.*?)<\/td>/is', $html, $matches)) {
            return null;
        }
        $content = $matches[1];
        $hasNoGigs = (stripos($content, 'There are no gigs') !== false);
        
        if ($hasNoGigs) {
            return [
                'date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                'day' => $day,
                'has_gigs' => false,
                'gigs' => []
            ];
        }
        return null; // Can't parse without DOM
    }
    
    $tdNode = $nodes->item(0);
    $content = $dom->saveHTML($tdNode);
    
    // Check if it says "There are no gigs"
    $hasNoGigs = (stripos($content, 'There are no gigs') !== false);
    
    if ($hasNoGigs) {
        return null; // Skip files with no gigs
    }
    
    // Extract the 2nd <p> tag
    $pTags = $xpath->query(".//p", $tdNode);
    if ($pTags->length < 2) {
        return null;
    }
    
    $secondP = $dom->saveHTML($pTags->item(1));
    
    // Strip all <font> and </font> tags first
    $secondP = preg_replace('/<\/?font[^>]*>/i', '', $secondP);
    
    // Concatenate lines
    $secondP = concatenateBrLines($secondP);
    
    // Remove the footer text - do this AFTER concatenation
    $secondP = preg_replace('/&gt;If you have a gig, on this date\.?\s*Let us know.*?gigs@saggy-pants\.com.*?<\/a>\.?/is', '', $secondP);
    $secondP = preg_replace('/If you have a gig, on this date\.?\s*Let us know.*?gigs@saggy-pants\.com.*?<\/a>\.?/is', '', $secondP);
    $secondP = preg_replace('/\s*&gt;If you have a gig.*?$/is', '', $secondP);
    $secondP = preg_replace('/<br>\s*&gt;If you have a gig.*?$/is', '', $secondP);
    
    // Remove align="left" attribute
    $secondP = preg_replace('/align=["\']left["\']/i', '', $secondP);
    $secondP = preg_replace('/<p\s+>/i', '<p>', $secondP);
    
    // Replace <br> with -- if neither side is empty space
    // Pattern: non-whitespace content before <br> and non-whitespace content after
    // This handles: content<br> content (not <br> <br> or content<br> <br>)
    $secondP = preg_replace('/([^\s<>])<br\s*\/?>\s*([^\s<>])/i', '$1 -- $2', $secondP);
    
    // Remove all remaining &gt; (including leading space if present)
    $secondP = preg_replace('/\s*&gt;/', '', $secondP);
    
    // Ensure all -- have a trailing space (normalize -- to -- )
    $secondP = preg_replace('/--([^\s])/', '-- $1', $secondP);
    
    // Clean up extra whitespace
    $secondP = preg_replace('/\s+/', ' ', $secondP);
    $secondP = trim($secondP);
    
    return [
        'date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
        'day' => $day,
        'month' => $month,
        'html' => $secondP
    ];
}

/**
 * Clean results array to ensure all strings are valid UTF-8
 */
function cleanResultsForJson($results) {
    foreach ($results as &$result) {
        $result['date'] = (string) $result['date'];
        $result['day'] = (int) $result['day'];
        $result['month'] = (int) $result['month'];
        
        // Clean HTML content
        if (isset($result['html']) && is_string($result['html'])) {
            $html = $result['html'];
            if (!mb_check_encoding($html, 'UTF-8')) {
                $html = @mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
                if (!mb_check_encoding($html, 'UTF-8')) {
                    $html = @mb_convert_encoding($html, 'UTF-8', 'auto');
                }
            }
            $html = @iconv('UTF-8', 'UTF-8//IGNORE', $html);
            $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $html);
            $html = str_replace(["\0", "\x00"], '', $html);
            $result['html'] = $html;
        }
    }
    return $results;
}

/**
 * Main execution
 */
function main() {
    global $gid_base_dir, $output_file, $month_map;
    
    if (!is_dir($gid_base_dir)) {
        die("Error: Directory not found: $gid_base_dir\n");
    }
    
    $results = [];
    
    // Get all month directories
    $month_dirs = glob($gid_base_dir . '/*', GLOB_ONLYDIR);
    
    foreach ($month_dirs as $month_dir) {
        $month_name = basename($month_dir);
        $month_num = $month_map[strtolower($month_name)] ?? null;
        
        if ($month_num === null) {
            continue; // Skip unknown month directories
        }
        
        // Get all .htm files in this month directory
        $files = glob($month_dir . '/*.htm');
        
        // Filter out calender.htm and other non-date files
        $files = array_filter($files, function($file) {
            $basename = basename($file);
            return preg_match('/^\d{1,2}\.htm$/', $basename);
        });
        
        // Sort files numerically by day
        usort($files, function($a, $b) {
            $dayA = (int) basename($a, '.htm');
            $dayB = (int) basename($b, '.htm');
            return $dayA <=> $dayB;
        });
        
        foreach ($files as $file) {
            $day = (int) basename($file, '.htm');
            $result = parseGigFile($file, $day, 2005, $month_num);
            
            // Only include files that have content
            if ($result !== null && !empty($result['html'])) {
                $results[] = $result;
            }
        }
    }
    
    // Sort results by date
    usort($results, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });
    
    // Clean results for JSON
    $results = cleanResultsForJson($results);
    
    // Output JSON
    $json = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE);
    
    if ($json === false) {
        die("Error encoding JSON: " . json_last_error_msg() . "\n");
    }
    
    // Save to file
    file_put_contents($output_file, $json);
    
    echo "Extraction complete! Processed " . count($results) . " files.\n";
    echo "Output saved to: $output_file\n";
}

// Run the script
error_reporting(E_ALL);
ini_set('display_errors', 1);

main();
