<?php

namespace SaggyPants;

class JukeboxUpdater {

    public static function init(): void {
        // AJAX handler for frontend button
        add_action('wp_ajax_saggy_pants_refresh', [self::class, 'ajaxRefresh']);

        // Add refresh button to jukebox post
        add_filter('the_content', [self::class, 'addRefreshButton'], 999);

        // Enqueue styles
        add_action('wp_enqueue_scripts', [self::class, 'enqueueAssets']);
    }

    /**
     * Main update function - scrapes audio page and updates the jukebox post
     * Returns array with 'success' and 'error' keys
     */
    public static function update(): array {
        $audioUrl = Settings::getAudioPageUrl();
        $postId = Settings::getJukeboxPostId();

        if (empty($audioUrl)) {
            return ['success' => false, 'error' => 'Audio page URL is not set. Go to Settings → Saggy Pants Jukebox.'];
        }

        if (empty($postId)) {
            return ['success' => false, 'error' => 'Jukebox Post ID is not set. Go to Settings → Saggy Pants Jukebox.'];
        }

        // Fetch the audio page
        $response = wp_remote_get($audioUrl, [
            'timeout' => 60,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            $msg = 'Failed to fetch audio page: ' . $response->get_error_message();
            error_log('Saggy Pants: ' . $msg);
            return ['success' => false, 'error' => $msg];
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        if ($statusCode !== 200) {
            $msg = 'Audio page returned HTTP ' . $statusCode;
            error_log('Saggy Pants: ' . $msg);
            return ['success' => false, 'error' => $msg];
        }

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) {
            return ['success' => false, 'error' => 'Empty response from audio page'];
        }

        // Parse and generate jukebox
        try {
            $generator = new PlaylistGenerator($html);
            $trackCount = $generator->getTrackCount();
        } catch (\Exception $e) {
            $msg = 'Parser error: ' . $e->getMessage();
            error_log('Saggy Pants: ' . $msg);
            return ['success' => false, 'error' => $msg];
        }

        if ($trackCount === 0) {
            return ['success' => false, 'error' => 'No tracks found in audio page. Check the page has wp-block-post items.'];
        }

        $newJukeboxBlock = $generator->getJukeboxBlock();

        // Get current post content
        $post = get_post($postId);
        if (!$post) {
            return ['success' => false, 'error' => 'Post ID ' . $postId . ' not found'];
        }

        // Replace existing jukebox block or append
        $content = $post->post_content;
        $pattern = '/<!-- wp:pinkcrab\/jukebox \{.*?\} \/-->/s';

        if (preg_match($pattern, $content)) {
            // Replace existing jukebox
            $newContent = preg_replace($pattern, $newJukeboxBlock, $content, 1);
            $action = 'replaced';
        } else {
            // Append jukebox at the end
            $newContent = $content . "\n\n" . $newJukeboxBlock;
            $action = 'appended';
        }

        // Update the post
        $result = wp_update_post([
            'ID' => $postId,
            'post_content' => $newContent,
        ], true);

        if (is_wp_error($result)) {
            $msg = 'Failed to update post: ' . $result->get_error_message();
            error_log('Saggy Pants: ' . $msg);
            return ['success' => false, 'error' => $msg];
        }

        // Log success
        error_log('Saggy Pants: Updated jukebox with ' . $trackCount . ' tracks');

        return [
            'success' => true,
            'track_count' => $trackCount,
            'post_id' => $postId,
            'action' => $action,
        ];
    }

    /**
     * AJAX handler for refresh button
     */
    public static function ajaxRefresh(): void {
        // Verify nonce
        if (!check_ajax_referer('saggy_pants_refresh', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }

        $result = self::update();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(['message' => $result['error']]);
        }
    }

    /**
     * Add refresh button to the jukebox post for logged-in admins
     */
    public static function addRefreshButton(string $content): string {
        if (!is_singular() || !current_user_can('edit_posts')) {
            return $content;
        }

        $postId = Settings::getJukeboxPostId();
        if (get_the_ID() !== $postId) {
            return $content;
        }

        $nonce = wp_create_nonce('saggy_pants_refresh');
        $button = sprintf(
            '<div class="saggy-pants-refresh-wrap" style="margin: 20px 0; text-align: center;">
                <button type="button" id="saggy-pants-refresh-btn" class="saggy-pants-refresh-btn" data-nonce="%s">
                    🔄 Refresh Jukebox
                </button>
                <span id="saggy-pants-status" style="margin-left: 10px;"></span>
            </div>',
            esc_attr($nonce)
        );

        return $button . $content;
    }

    /**
     * Enqueue frontend assets
     */
    public static function enqueueAssets(): void {
        $postId = Settings::getJukeboxPostId();
        if (!is_singular() || get_the_ID() !== $postId || !current_user_can('edit_posts')) {
            return;
        }

        // Inline CSS
        wp_add_inline_style('wp-block-library', '
            .saggy-pants-refresh-btn {
                background: #2271b1;
                color: #fff;
                border: none;
                padding: 10px 20px;
                font-size: 14px;
                cursor: pointer;
                border-radius: 4px;
                transition: background 0.2s;
            }
            .saggy-pants-refresh-btn:hover {
                background: #135e96;
            }
            .saggy-pants-refresh-btn:disabled {
                background: #a0a5aa;
                cursor: not-allowed;
            }
            .jukebox__track-cover img {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
        ');

        // Inline JS
        wp_add_inline_script('wp-block-library', '
            document.addEventListener("DOMContentLoaded", function() {
                var btn = document.getElementById("saggy-pants-refresh-btn");
                var status = document.getElementById("saggy-pants-status");
                if (!btn) return;

                btn.addEventListener("click", function() {
                    btn.disabled = true;
                    btn.textContent = "⏳ Updating...";
                    status.textContent = "";

                    fetch("' . admin_url('admin-ajax.php') . '", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "action=saggy_pants_refresh&nonce=" + btn.dataset.nonce
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            status.textContent = "✅ Updated " + data.data.track_count + " tracks!";
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            status.textContent = "❌ " + (data.data.message || "Failed");
                            btn.disabled = false;
                            btn.textContent = "🔄 Refresh Jukebox";
                        }
                    })
                    .catch(function(err) {
                        status.textContent = "❌ Error: " + err.message;
                        btn.disabled = false;
                        btn.textContent = "🔄 Refresh Jukebox";
                    });
                });
            });
        ');
    }
}

