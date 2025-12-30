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

function rs_agents_normalize_handle($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    $value = preg_replace('#^https?://#i', '', $value);
    $value = preg_replace('#^www\.#i', '', $value);
    $value = preg_replace('#^instagram\.com/#i', '', $value);
    $value = preg_replace('#^t\.me/#i', '', $value);
    $value = preg_replace('#^telegram\.me/#i', '', $value);
    $value = preg_replace('#^@#', '', $value);
    $value = trim($value, "/ \t\n\r\0\x0B");
    return $value;
}

function rs_agents_normalize_iran_phone($value) {
    $digits = preg_replace('/\D+/', '', (string) $value);
    if ($digits === '') {
        return '';
    }
    if (strpos($digits, '0098') === 0) {
        $digits = substr($digits, 2);
    }
    if (strpos($digits, '98') === 0) {
        return $digits;
    }
    if (strpos($digits, '0') === 0 && strlen($digits) === 11) {
        return '98' . substr($digits, 1);
    }
    if (strpos($digits, '9') === 0 && strlen($digits) === 10) {
        return '98' . $digits;
    }
    return $digits;
}

function rs_agents_format_url_with_scheme($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    return 'https://' . $value;
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
            $type = isset($agent['type']) ? esc_attr($agent['type']) : '';
            $company = isset($agent['company']) ? esc_attr($agent['company']) : '';
            $website = isset($agent['website']) ? esc_attr($agent['website']) : '';
            $instagram = isset($agent['instagram']) ? esc_attr($agent['instagram']) : '';
            $mobile = isset($agent['mobile']) ? esc_attr($agent['mobile']) : '';
            $phone = isset($agent['phone']) ? esc_attr($agent['phone']) : '';
            $address = isset($agent['address']) ? esc_textarea($agent['address']) : '';
            $whatsapp = isset($agent['whatsapp']) ? esc_attr($agent['whatsapp']) : '';
            $telegram = isset($agent['telegram']) ? esc_attr($agent['telegram']) : '';
            $order = isset($agent['order']) ? (int) $agent['order'] : ($index + 1);
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
                        نوع
                        <select name="rs_agents[<?php echo esc_attr($index); ?>][type]">
                            <option value="">انتخاب کنید</option>
                            <option value="نماینده فروش (انحصاری)" <?php selected($type, 'نماینده فروش (انحصاری)'); ?>>نماینده فروش (انحصاری)</option>
                            <option value="عامل فروش" <?php selected($type, 'عامل فروش'); ?>>عامل فروش</option>
                        </select>
                    </label>
                    <label>
                        نام فروشگاه/شرکت
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][company]" value="<?php echo $company; ?>" />
                    </label>
                    <label>
                        آدرس سایت
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][website]" value="<?php echo $website; ?>" />
                    </label>
                    <label>
                        آی دی اینستاگرام
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][instagram]" value="<?php echo $instagram; ?>" />
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
                        شماره واتساپ
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][whatsapp]" value="<?php echo $whatsapp; ?>" />
                    </label>
                    <label>
                        آی دی تلگرام
                        <input type="text" name="rs_agents[<?php echo esc_attr($index); ?>][telegram]" value="<?php echo $telegram; ?>" />
                    </label>
                    <label>
                        ترتیب نمایش
                        <input type="number" min="1" name="rs_agents[<?php echo esc_attr($index); ?>][order]" value="<?php echo esc_attr($order); ?>" />
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
                    نوع
                    <select name="rs_agents[__INDEX__][type]">
                        <option value="">انتخاب کنید</option>
                        <option value="نماینده فروش (انحصاری)">نماینده فروش (انحصاری)</option>
                        <option value="عامل فروش">عامل فروش</option>
                    </select>
                </label>
                <label>
                    نام فروشگاه/شرکت
                    <input type="text" name="rs_agents[__INDEX__][company]" />
                </label>
                <label>
                    آدرس سایت
                    <input type="text" name="rs_agents[__INDEX__][website]" />
                </label>
                <label>
                    آی دی اینستاگرام
                    <input type="text" name="rs_agents[__INDEX__][instagram]" />
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
                    شماره واتساپ
                    <input type="text" name="rs_agents[__INDEX__][whatsapp]" />
                </label>
                <label>
                    آی دی تلگرام
                    <input type="text" name="rs_agents[__INDEX__][telegram]" />
                </label>
                <label>
                    ترتیب نمایش
                    <input type="number" min="1" name="rs_agents[__INDEX__][order]" value="__ORDER__" />
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
        $type = isset($agent['type']) ? sanitize_text_field($agent['type']) : '';
        $company = isset($agent['company']) ? sanitize_text_field($agent['company']) : '';
        $website = isset($agent['website']) ? sanitize_text_field($agent['website']) : '';
        $instagram = isset($agent['instagram']) ? sanitize_text_field($agent['instagram']) : '';
        $mobile = isset($agent['mobile']) ? sanitize_text_field($agent['mobile']) : '';
        $phone = isset($agent['phone']) ? sanitize_text_field($agent['phone']) : '';
        $address = isset($agent['address']) ? sanitize_textarea_field($agent['address']) : '';
        $whatsapp = isset($agent['whatsapp']) ? sanitize_text_field($agent['whatsapp']) : '';
        $telegram = isset($agent['telegram']) ? sanitize_text_field($agent['telegram']) : '';
        $image_id = isset($agent['image_id']) ? (int) $agent['image_id'] : 0;
        $order = isset($agent['order']) ? (int) $agent['order'] : 0;

        if ($name === '' && $mobile === '' && $phone === '' && $address === '' && $company === '' && $website === '') {
            continue;
        }

        $sanitized[] = [
            'name' => $name,
            'type' => $type,
            'company' => $company,
            'website' => $website,
            'instagram' => $instagram,
            'mobile' => $mobile,
            'phone' => $phone,
            'address' => $address,
            'whatsapp' => $whatsapp,
            'telegram' => $telegram,
            'image_id' => $image_id,
            'order' => $order,
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

function rs_agents_sort_by_order($a, $b) {
    $order_a = isset($a['order']) ? (int) $a['order'] : 0;
    $order_b = isset($b['order']) ? (int) $b['order'] : 0;
    $order_a = $order_a > 0 ? $order_a : PHP_INT_MAX;
    $order_b = $order_b > 0 ? $order_b : PHP_INT_MAX;

    if ($order_a === $order_b) {
        return 0;
    }

    return ($order_a < $order_b) ? -1 : 1;
}

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
            usort($agents, 'rs_agents_sort_by_order');
            foreach ($agents as $agent) {
                $agent['order'] = isset($agent['order']) ? (int) $agent['order'] : 0;
                $agent['city'] = $post_item->post_title;
                $agents_data[] = $agent;
            }
        }
    } else {
        $post_id = (int) $atts['post_id'];
        $agents = get_post_meta($post_id, RS_AGENTS_META_KEY, true);
        if (is_array($agents)) {
            $city_name = get_the_title($post_id);
            usort($agents, 'rs_agents_sort_by_order');
            foreach ($agents as $agent) {
                $agent['order'] = isset($agent['order']) ? (int) $agent['order'] : 0;
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
                $type = isset($agent['type']) ? esc_html($agent['type']) : '';
                $company = isset($agent['company']) ? esc_html($agent['company']) : '';
                $mobile = isset($agent['mobile']) ? esc_html($agent['mobile']) : '';
                $phone = isset($agent['phone']) ? esc_html($agent['phone']) : '';
                $address = isset($agent['address']) ? esc_html($agent['address']) : '';
                $website = isset($agent['website']) ? rs_agents_format_url_with_scheme($agent['website']) : '';
                $instagram_handle = isset($agent['instagram']) ? rs_agents_normalize_handle($agent['instagram']) : '';
                $whatsapp_value = isset($agent['whatsapp']) ? $agent['whatsapp'] : '';
                $telegram_handle = isset($agent['telegram']) ? rs_agents_normalize_handle($agent['telegram']) : '';
                $whatsapp = '';
                $telegram = '';
                $instagram = '';
                $site = '';
                if ($website) {
                    $site = esc_url($website);
                }
                if ($instagram_handle !== '') {
                    $instagram = esc_url('https://instagram.com/' . $instagram_handle);
                }
                if ($telegram_handle !== '') {
                    $telegram = esc_url('https://t.me/' . $telegram_handle);
                }
                if ($whatsapp_value) {
                    if (preg_match('#^https?://#i', $whatsapp_value)) {
                        $whatsapp = esc_url($whatsapp_value);
                    } else {
                        $normalized = rs_agents_normalize_iran_phone($whatsapp_value);
                        if ($normalized !== '') {
                            $whatsapp = esc_url('https://wa.me/' . $normalized);
                        }
                    }
                }
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
                            <?php if ($company) : ?>
                                <p class="rs-agent-company"><?php echo $company; ?></p>
                            <?php endif; ?>
                            <?php if ($type) : ?>
                                <p class="rs-agent-type"><?php echo $type; ?></p>
                            <?php endif; ?>
                            <p class="rs-agent-city"><?php echo $city; ?></p>
                            <div class="rs-agent-contacts">
                                <?php if ($site) : ?>
                                    <a class="rs-agent-contact-link" href="<?php echo $site; ?>" target="_blank" rel="noopener" aria-label="سایت">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm6.93 9h-3.07a15.8 15.8 0 00-1.25-5.02A8.03 8.03 0 0118.93 11zM12 4.07c.9 1.2 1.7 3.22 1.97 4.93h-3.94C10.3 7.29 11.1 5.27 12 4.07zM5.07 13h3.07c.25 1.82.79 3.55 1.58 4.88A8.03 8.03 0 015.07 13zm3.07-2H5.07a8.03 8.03 0 014.58-5.02A15.8 15.8 0 008.14 11zm3.86 8.93c-.92-1.21-1.74-3.26-2.02-4.93h4.04c-.28 1.67-1.1 3.72-2.02 4.93zM15.35 18a15.87 15.87 0 001.51-5h3.07A8.03 8.03 0 0115.35 18zm-1.29-7H9.94c-.3 1.87-.26 3.71 0 5h4.12c.26-1.29.3-3.13 0-5z"/></svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($instagram) : ?>
                                    <a class="rs-agent-contact-link" href="<?php echo $instagram; ?>" target="_blank" rel="noopener" aria-label="اینستاگرام">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 3h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7a4 4 0 014-4zm0 2a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H7zm5 3.5A4.5 4.5 0 1112 17.5 4.5 4.5 0 0112 8.5zm0 2A2.5 2.5 0 1014.5 13 2.5 2.5 0 0012 10.5zm5.25-3.75a1 1 0 11-1 1 1 1 0 011-1z"/></svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($telegram) : ?>
                                    <a class="rs-agent-contact-link" href="<?php echo $telegram; ?>" target="_blank" rel="noopener" aria-label="تلگرام">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21.6 4.2a1 1 0 00-1.04-.19L2.6 11.1a1 1 0 00.06 1.87l4.46 1.7 1.7 5.36a1 1 0 001.65.4l2.8-2.8 4.78 3.52a1 1 0 001.57-.6l3.02-14a1 1 0 00-.04-.35zM8.9 13.7l8.96-5.62-6.76 6.92-.25 2.76-1.32-4.06-.63-.23z"/></svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($whatsapp) : ?>
                                    <a class="rs-agent-contact-link" href="<?php echo $whatsapp; ?>" target="_blank" rel="noopener" aria-label="واتساپ">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 00-8.94 14.5L2 22l5.7-1.02A10 10 0 1012 2zm0 2a8 8 0 016.93 12.02.96.96 0 00-.1.67l.54 2.7-2.76-.5a1 1 0 00-.62.09A8 8 0 114 12a8 8 0 018-8zm3.73 9.73c-.2-.1-1.18-.58-1.36-.65-.18-.06-.32-.1-.46.1-.14.2-.52.65-.64.78-.12.13-.24.14-.44.04-.2-.1-.86-.32-1.63-1.02-.6-.54-1-1.2-1.12-1.4-.12-.2-.01-.3.09-.4.1-.1.2-.24.3-.36.1-.12.14-.2.22-.34.08-.14.04-.26-.02-.36-.06-.1-.46-1.1-.63-1.5-.17-.4-.34-.35-.46-.36h-.4c-.14 0-.36.05-.55.26-.18.2-.72.7-.72 1.7s.74 1.98.84 2.12c.1.14 1.45 2.2 3.52 3.08.5.22.9.36 1.2.46.5.16.96.14 1.32.08.4-.06 1.18-.48 1.34-.94.17-.46.17-.86.12-.94-.05-.08-.18-.12-.38-.22z"/></svg>
                                    </a>
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
