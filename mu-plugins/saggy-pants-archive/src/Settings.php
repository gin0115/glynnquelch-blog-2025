<?php

namespace SaggyPants;

class Settings {

    const OPTION_KEY = 'saggy_pants_settings';

    public static function init(): void {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_init', [self::class, 'registerSettings']);
    }

    public static function addMenu(): void {
        add_options_page(
            'Saggy Pants Jukebox',
            'Saggy Pants Jukebox',
            'manage_options',
            'saggy-pants',
            [self::class, 'renderPage']
        );
    }

    public static function registerSettings(): void {
        register_setting(self::OPTION_KEY, self::OPTION_KEY, [
            'sanitize_callback' => [self::class, 'sanitize'],
        ]);

        add_settings_section(
            'saggy_pants_main',
            'Jukebox Settings',
            null,
            'saggy-pants'
        );

        add_settings_field(
            'audio_page_url',
            'Audio Archive Page URL',
            [self::class, 'renderUrlField'],
            'saggy-pants',
            'saggy_pants_main'
        );

        add_settings_field(
            'jukebox_post_id',
            'Jukebox Post ID',
            [self::class, 'renderPostIdField'],
            'saggy-pants',
            'saggy_pants_main'
        );
    }

    public static function sanitize(array $input): array {
        return [
            'audio_page_url' => esc_url_raw($input['audio_page_url'] ?? ''),
            'jukebox_post_id' => absint($input['jukebox_post_id'] ?? 0),
        ];
    }

    public static function renderUrlField(): void {
        $options = self::get();
        printf(
            '<input type="url" name="%s[audio_page_url]" value="%s" class="regular-text" placeholder="https://example.com/archive/audio/" />',
            self::OPTION_KEY,
            esc_attr($options['audio_page_url'])
        );
        echo '<p class="description">The page that lists all audio posts to scrape.</p>';
    }

    public static function renderPostIdField(): void {
        $options = self::get();
        printf(
            '<input type="number" name="%s[jukebox_post_id]" value="%s" class="small-text" min="1" />',
            self::OPTION_KEY,
            esc_attr($options['jukebox_post_id'])
        );
        echo '<p class="description">The post ID where the jukebox block will be updated.</p>';
    }

    public static function renderPage(): void {
        if (!current_user_can('manage_options')) return;

        // Handle manual update
        if (isset($_POST['saggy_pants_manual_update']) && check_admin_referer('saggy_pants_manual')) {
            $result = JukeboxUpdater::update();
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>✅ Jukebox updated successfully! <strong>' . esc_html($result['track_count']) . '</strong> tracks ' . esc_html($result['action']) . '.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Failed to update jukebox: <strong>' . esc_html($result['error']) . '</strong></p></div>';
            }
        }

        $options = self::get();
        ?>
        <div class="wrap">
            <h1>Saggy Pants Jukebox Settings</h1>

            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_KEY);
                do_settings_sections('saggy-pants');
                submit_button('Save Settings');
                ?>
            </form>

            <hr>

            <h2>Manual Update</h2>
            <h3>Current Configuration:</h3>
            <table class="widefat" style="max-width: 600px;">
                <tr>
                    <th>Audio Page URL</th>
                    <td><?php echo $options['audio_page_url'] ? '<code>' . esc_html($options['audio_page_url']) . '</code>' : '<span style="color:red;">⚠️ Not set</span>'; ?></td>
                </tr>
                <tr>
                    <th>Jukebox Post ID</th>
                    <td>
                        <?php if ($options['jukebox_post_id']) : ?>
                            <code><?php echo esc_html($options['jukebox_post_id']); ?></code>
                            <?php
                            $post = get_post($options['jukebox_post_id']);
                            if ($post) {
                                echo ' - <a href="' . get_permalink($post) . '" target="_blank">' . esc_html($post->post_title) . '</a>';
                                echo ' (<a href="' . get_edit_post_link($post) . '">edit</a>)';
                            } else {
                                echo ' <span style="color:red;">⚠️ Post not found!</span>';
                            }
                            ?>
                        <?php else : ?>
                            <span style="color:red;">⚠️ Not set</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p style="margin-top: 20px;">Click the button below to manually scrape the audio page and update the jukebox now.</p>
            <form method="post">
                <?php wp_nonce_field('saggy_pants_manual'); ?>
                <button type="submit" name="saggy_pants_manual_update" class="button button-primary">
                    Update Jukebox Now
                </button>
            </form>

            <hr>

            <h2>Scheduled Updates</h2>
            <?php
            $next = as_next_scheduled_action('saggy_pants_update_jukebox');
            if ($next) {
                echo '<p>Next scheduled update: <strong>' . esc_html(wp_date('Y-m-d H:i:s', $next)) . '</strong></p>';
            } else {
                echo '<p>No scheduled update found. It will be scheduled on next page load.</p>';
            }
            ?>
        </div>
        <?php
    }

    public static function get(): array {
        $defaults = [
            'audio_page_url' => '',
            'jukebox_post_id' => 0,
        ];
        return wp_parse_args(get_option(self::OPTION_KEY, []), $defaults);
    }

    public static function getAudioPageUrl(): string {
        return self::get()['audio_page_url'];
    }

    public static function getJukeboxPostId(): int {
        return (int) self::get()['jukebox_post_id'];
    }
}

