<?php
/**
 * Plugin Name: Reseller Agents Cards
 * Description: Adds reseller agents meta box with card display and search shortcode.
 * Version: 1.0.0
 * Author: OpenAI
 */

if (!defined('ABSPATH')) {
    exit;
}

const RS_AGENTS_META_KEY = '_rs_agents';
const RS_AGENTS_ENABLED_META_KEY = '_rs_agents_enabled';

function rs_agents_get_default_avatar_url() {
    return plugin_dir_url(__FILE__) . 'assets/default-avatar.svg';
}

function rs_agents_register_meta_box() {
    add_meta_box(
        'rs-agents-meta-box',
        'نمایندگان فروش',
        'rs_agents_render_meta_box',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'rs_agents_register_meta_box');

function rs_agents_enqueue_admin_assets($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    wp_enqueue_style(
        'rs-agents-admin',
        plugin_dir_url(__FILE__) . 'assets/admin.css',
        [],
        '1.0.0'
    );

    wp_enqueue_media();
    wp_enqueue_script(
        'rs-agents-admin',
        plugin_dir_url(__FILE__) . 'assets/admin.js',
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'rs_agents_enqueue_admin_assets');

function rs_agents_render_meta_box($post) {
    wp_nonce_field('rs_agents_save_meta', 'rs_agents_nonce');

    $agents = get_post_meta($post->ID, RS_AGENTS_META_KEY, true);
    if (!is_array($agents)) {
        $agents = [];
    }
    $enabled = get_post_meta($post->ID, RS_AGENTS_ENABLED_META_KEY, true) === '1';
    $fields_style = $enabled ? '' : 'style="display:none;"';
    ?>
    <label>
        <input type="checkbox" name="rs_agents_enabled" value="1" data-agents-toggle <?php checked($enabled); ?> />
        اضافه کردن نماینده یا عامل فروش
    </label>
    <div class="rs-agents-meta-box" data-agents-container>
        <div data-agents-fields <?php echo $fields_style; ?>>
            <?php foreach ($agents as $index => $agent) :
            $name = isset($agent['name']) ? esc_attr($agent['name']) : '';
            $mobile = isset($agent['mobile']) ? esc_attr($agent['mobile']) : '';
            $phone = isset($agent['phone']) ? esc_attr($agent['phone']) : '';
            $address = isset($agent['address']) ? esc_textarea($agent['address']) : '';
            $whatsapp = isset($agent['whatsapp']) ? esc_url($agent['whatsapp']) : '';
            $telegram = isset($agent['telegram']) ? esc_url($agent['telegram']) : '';
            $image_id = isset($agent['image_id']) ? (int) $agent['image_id'] : 0;
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
            ?>
            <div class="rs-agent-item" data-agent-item>
                <div class="rs-agent-fields">
                    <label>
                        نام نماینده
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][name]" value="<?php echo $name; ?>" />
                    </label>
                    <label>
                        شماره موبایل
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][mobile]" value="<?php echo $mobile; ?>" />
                    </label>
                    <label>
                        شماره تلفن
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][phone]" value="<?php echo $phone; ?>" />
                    </label>
                    <label>
                        آدرس
                        <textarea name="rs_agents[<?php echo esc_attr($index); ?>][address]" rows="3"><?php echo $address; ?></textarea>
                    </label>
                    <label>
                        لینک واتساپ
                        <input type="url" name="rs_agents[<?php echo esc_attr($index); ?>][whatsapp]" value="<?php echo $whatsapp; ?>" />
                    </label>
                    <label>
                        لینک تلگرام
                        <input type="url" name="rs_agents[<?php echo esc_attr($index); ?>][telegram]" value="<?php echo $telegram; ?>" />
                    </label>
                </div>
                <div class="rs-agent-media">
                    <div class="rs-agent-preview" data-image-preview>
                        <?php if ($image_url) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="" />
                        <?php else : ?>
                            <span>بدون تصویر</span>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="rs_agents[<?php echo esc_attr($index); ?>][image_id]" value="<?php echo esc_attr($image_id); ?>" data-image-id />
                    <button type="button" class="button rs-agent-upload" data-upload-button>انتخاب تصویر</button>
                    <button type="button" class="button rs-agent-remove-image" data-remove-image>حذف تصویر</button>
                </div>
                <button type="button" class="button-link rs-agent-remove" data-remove-agent>حذف نماینده</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <button type="button" class="button button-primary" data-add-agent <?php echo $fields_style; ?>>افزودن نماینده</button>

    <template id="rs-agent-template">
        <div class="rs-agent-item" data-agent-item>
            <div class="rs-agent-fields">
                <label>
                    نام نماینده
                    <input type="text" name="rs_agents[__INDEX__][name]" />
                </label>
                <label>
                    شماره موبایل
                    <input type="text" name="rs_agents[__INDEX__][mobile]" />
                </label>
                <label>
                    شماره تلفن
                    <input type="text" name="rs_agents[__INDEX__][phone]" />
                </label>
                <label>
                    آدرس
                    <textarea name="rs_agents[__INDEX__][address]" rows="3"></textarea>
                </label>
                <label>
                    لینک واتساپ
                    <input type="url" name="rs_agents[__INDEX__][whatsapp]" />
                </label>
                <label>
                    لینک تلگرام
                    <input type="url" name="rs_agents[__INDEX__][telegram]" />
                </label>
            </div>
            <div class="rs-agent-media">
                <div class="rs-agent-preview" data-image-preview>
                    <span>بدون تصویر</span>
                </div>
                <input type="hidden" name="rs_agents[__INDEX__][image_id]" value="0" data-image-id />
                <button type="button" class="button rs-agent-upload" data-upload-button>انتخاب تصویر</button>
                <button type="button" class="button rs-agent-remove-image" data-remove-image>حذف تصویر</button>
            </div>
            <button type="button" class="button-link rs-agent-remove" data-remove-agent>حذف نماینده</button>
        </div>
    </template>
    <?php
}

function rs_agents_save_meta($post_id) {
    if (!isset($_POST['rs_agents_nonce']) || !wp_verify_nonce($_POST['rs_agents_nonce'], 'rs_agents_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $enabled = isset($_POST['rs_agents_enabled']) && $_POST['rs_agents_enabled'] === '1';
    if (!$enabled) {
        delete_post_meta($post_id, RS_AGENTS_META_KEY);
        delete_post_meta($post_id, RS_AGENTS_ENABLED_META_KEY);
        return;
    }

    update_post_meta($post_id, RS_AGENTS_ENABLED_META_KEY, '1');

    if (!isset($_POST['rs_agents']) || !is_array($_POST['rs_agents'])) {
        delete_post_meta($post_id, RS_AGENTS_META_KEY);
        return;
    }

    $sanitized = [];
    foreach ($_POST['rs_agents'] as $agent) {
        $name = isset($agent['name']) ? sanitize_text_field($agent['name']) : '';
        $mobile = isset($agent['mobile']) ? sanitize_text_field($agent['mobile']) : '';
        $phone = isset($agent['phone']) ? sanitize_text_field($agent['phone']) : '';
        $address = isset($agent['address']) ? sanitize_textarea_field($agent['address']) : '';
        $whatsapp = isset($agent['whatsapp']) ? esc_url_raw($agent['whatsapp']) : '';
        $telegram = isset($agent['telegram']) ? esc_url_raw($agent['telegram']) : '';
        $image_id = isset($agent['image_id']) ? (int) $agent['image_id'] : 0;

        if ($name === '' && $mobile === '' && $phone === '' && $address === '') {
            continue;
        }

        $sanitized[] = [
            'name' => $name,
            'mobile' => $mobile,
            'phone' => $phone,
            'address' => $address,
            'whatsapp' => $whatsapp,
            'telegram' => $telegram,
            'image_id' => $image_id,
        ];
    }

    if ($sanitized) {
        update_post_meta($post_id, RS_AGENTS_META_KEY, $sanitized);
    } else {
        delete_post_meta($post_id, RS_AGENTS_META_KEY);
    }
}
add_action('save_post', 'rs_agents_save_meta');

function rs_agents_enqueue_frontend_assets() {
    wp_enqueue_style(
        'rs-agents-frontend',
        plugin_dir_url(__FILE__) . 'assets/frontend.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'rs-agents-frontend',
        plugin_dir_url(__FILE__) . 'assets/frontend.js',
        [],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'rs_agents_enqueue_frontend_assets');

function rs_agents_render_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'post_id' => get_the_ID(),
            'all' => '0',
        ],
        $atts,
        'reseller_agents'
    );

    $agents_data = [];
    if ($atts['all'] === '1') {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => RS_AGENTS_META_KEY,
        ]);

        foreach ($posts as $post_item) {
            $agents = get_post_meta($post_item->ID, RS_AGENTS_META_KEY, true);
            if (!is_array($agents)) {
                continue;
            }
            foreach ($agents as $agent) {
                $agent['city'] = $post_item->post_title;
                $agents_data[] = $agent;
            }
        }
    } else {
        $post_id = (int) $atts['post_id'];
        $agents = get_post_meta($post_id, RS_AGENTS_META_KEY, true);
        if (is_array($agents)) {
            $city_name = get_the_title($post_id);
            foreach ($agents as $agent) {
                $agent['city'] = $city_name;
                $agents_data[] = $agent;
            }
        }
    }

    if (!$agents_data) {
        return '<div class="rs-agents-empty">نماینده‌ای برای نمایش وجود ندارد.</div>';
    }

    ob_start();
    ?>
    <div class="rs-agents">
        <div class="rs-agents-search">
            <input type="text" placeholder="جستجو بر اساس شهر یا نماینده" data-agent-search />
        </div>
        <div class="rs-agents-grid" data-agent-grid>
            <?php foreach ($agents_data as $agent) :
                $name = isset($agent['name']) ? esc_html($agent['name']) : '';
                $mobile = isset($agent['mobile']) ? esc_html($agent['mobile']) : '';
                $phone = isset($agent['phone']) ? esc_html($agent['phone']) : '';
                $address = isset($agent['address']) ? esc_html($agent['address']) : '';
                $whatsapp = isset($agent['whatsapp']) ? esc_url($agent['whatsapp']) : '';
                $telegram = isset($agent['telegram']) ? esc_url($agent['telegram']) : '';
                $image_id = isset($agent['image_id']) ? (int) $agent['image_id'] : 0;
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
                $avatar_url = $image_url ? $image_url : rs_agents_get_default_avatar_url();
                $city = isset($agent['city']) ? esc_html($agent['city']) : '';
                ?>
                <div class="rs-agent-card" data-agent-card data-name="<?php echo esc_attr($name); ?>" data-city="<?php echo esc_attr($city); ?>">
                    <div class="rs-agent-card-info">
                        <div class="rs-agent-row">
                            <span class="rs-agent-icon">📱</span>
                            <span class="rs-agent-text">
                                <?php if ($mobile) : ?>
                                    <a href="tel:<?php echo esc_attr($mobile); ?>"><?php echo $mobile; ?></a>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="rs-agent-row">
                            <span class="rs-agent-icon">☎️</span>
                            <span class="rs-agent-text">
                                <?php if ($phone) : ?>
                                    <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo $phone; ?></a>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="rs-agent-row">
                            <span class="rs-agent-icon">📍</span>
                            <span class="rs-agent-text"><?php echo $address; ?></span>
                        </div>
                    </div>
                    <div class="rs-agent-card-main">
                        <div class="rs-agent-details">
                            <h3><?php echo $name; ?></h3>
                            <p class="rs-agent-city"><?php echo $city; ?></p>
                            <div class="rs-agent-contacts">
                                <?php if ($whatsapp) : ?>
                                    <a href="<?php echo $whatsapp; ?>" target="_blank" rel="noopener">WhatsApp</a>
                                <?php endif; ?>
                                <?php if ($telegram) : ?>
                                    <a href="<?php echo $telegram; ?>" target="_blank" rel="noopener">Telegram</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="rs-agent-avatar">
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($name ? $name : 'نماینده'); ?>" />
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('reseller_agents', 'rs_agents_render_shortcode');
