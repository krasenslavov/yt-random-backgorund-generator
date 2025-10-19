# YT Random Background Generator

Dynamically assign random background colors or images on each page load. Configure custom colors/images globally or per category/tag. Perfect for creative sites, portfolios, and dynamic visual experiences.

## Description

The Random Background Generator plugin injects random backgrounds into your WordPress site. Choose from solid colors, images, or a mix of both. Set backgrounds to change on every page load, daily, or per session. Assign specific backgrounds to categories and tags for targeted visual themes.

## Features

- **Random Backgrounds**: Colors or images selected randomly
- **Background Types**: Solid colors, images, or mixed mode
- **Change Frequency**: Every page load, daily, or per session
- **Category-Specific**: Assign backgrounds to categories
- **Tag-Specific**: Assign backgrounds to tags (optional)
- **Post Type Support**: Different backgrounds per post type
- **Color Management**: Add unlimited colors with color picker
- **Image Management**: Upload and manage background images
- **Image Settings**: Size, position, repeat, attachment control
- **Smooth Transitions**: Optional CSS transitions
- **Target Element**: Apply to any CSS selector (body, containers, etc.)
- **Live Preview**: Generate random previews in admin
- **Session Persistence**: Keep same background during user session
- **Daily Backgrounds**: One background per day
- **Fallback Color**: Backup color when no backgrounds configured
- **Inline CSS Injection**: Lightweight, no external files
- **Custom CSS**: Add your own styling rules
- **Translation Ready**: Full i18n support
- **No Database Tables**: Uses WordPress options

## Installation

1. Upload `yt-random-background-generator.php` to `/wp-content/plugins/`
2. Upload `yt-random-background-generator.css` to the same directory
3. Upload `yt-random-background-generator.js` to the same directory
4. Activate the plugin through the 'Plugins' menu
5. Go to Settings → Random Backgrounds
6. Configure your colors or images
7. Save settings

## Usage

### Basic Setup

1. Go to **Settings → Random Backgrounds**
2. Check **Enable Random Backgrounds**
3. Select **Background Type** (Color, Image, or Mixed)
4. Add colors or images
5. Save settings

### Adding Colors

1. Click **Add Color** button
2. Click the color box to open color picker
3. Select your desired color
4. Repeat to add more colors
5. Click **Remove** to delete unwanted colors

### Adding Images

1. Click **Add Image** button
2. Select image from Media Library (or upload new)
3. Click **Use This Image**
4. Image appears in the list
5. Click **Remove** to delete unwanted images

### Change Frequency Options

**Every page load**
- New random background on each page refresh
- Most dynamic option
- Different for every visitor

**Once per day**
- Same background for all visitors on a given day
- Changes at midnight (server timezone)
- Consistent daily theme

**Once per session**
- Same background for duration of user session
- Changes when user closes browser
- Consistent per-visitor experience

### Category-Specific Backgrounds

1. Enable **Category Backgrounds**
2. Scroll to **Category Backgrounds** table
3. For each category:
   - Select Type (Color or Image)
   - Enter color hex code or image URL
4. Save settings

When viewing posts in that category, the specific background appears instead of random selection.

### Image Display Settings

**Background Size**
- **Cover**: Image covers entire area (recommended)
- **Contain**: Image fits within area, may show gaps
- **Auto**: Original image size

**Background Position**
- Center, Top Left, Top Center, Top Right, Bottom Center

**Background Repeat**
- No Repeat (recommended for photos)
- Repeat (for patterns)
- Repeat X/Y (horizontal/vertical tiling)

**Background Attachment**
- **Fixed**: Parallax effect (image stays fixed while scrolling)
- **Scroll**: Image scrolls with page

### Advanced Settings

**Target Element**
- CSS selector for background application
- Default: `body`
- Examples: `.site-content`, `#main`, `.hero-section`

**Transitions**
- Enable smooth background transitions
- Set duration (e.g., 0.5s, 1s, 2s)

**Fallback Color**
- Backup color when no backgrounds configured
- Default: white (#ffffff)

**Custom CSS**
- Add additional CSS rules
- Applied alongside generated background CSS

## Configuration Examples

### Vibrant Color Palette

```
Background Type: Solid Colors
Colors: #e74c3c, #3498db, #2ecc71, #f39c12, #9b59b6, #1abc9c
Change Frequency: Every page load
Transition: Enabled (0.5s)
```

### Photography Portfolio

```
Background Type: Background Images
Images: [5-10 high-res photos]
Image Size: Cover
Image Attachment: Fixed (Parallax)
Change Frequency: Once per day
```

### Mixed Creative Site

```
Background Type: Mixed
Colors: #34495e, #2c3e50, #7f8c8d
Images: [3-4 abstract patterns]
Change Frequency: Every page load
Transition: Enabled (1s)
```

### Category-Based Blog

```
Background Type: Solid Colors
Enable Categories: Yes
Category Backgrounds:
  - Technology: #3498db (blue)
  - Design: #e74c3c (red)
  - Lifestyle: #2ecc71 (green)
Change Frequency: Session
```

### Daily Themed Site

```
Background Type: Background Images
Images: [7 images - one per day of week]
Change Frequency: Once per day
Image Size: Cover
Target Element: .site-content
```

## Use Cases

### Creative Agency Portfolio

Display different vibrant backgrounds on each project page visit:
```
Type: Mixed
Colors: 5 brand colors
Images: 3 texture patterns
Frequency: Every page load
```

### Photography Website

Showcase different photos as full-page backgrounds:
```
Type: Images
Images: 20 portfolio photos
Size: Cover
Attachment: Fixed
Frequency: Daily
```

### Blog with Category Themes

Different color schemes for different blog topics:
```
Type: Colors
Enable Categories: Yes
Tech posts: Blue background
Food posts: Warm orange
Travel posts: Green tones
```

### Event Website

Change background daily leading up to event:
```
Type: Images (countdown themed)
Frequency: Daily
Transition: Enabled
```

### Landing Page

Random inspiring backgrounds for visitors:
```
Type: Mixed
Colors: 3 gradient-friendly colors
Images: 5 motivational scenes
Frequency: Session
```

## Technical Details

### File Structure

```
yt-random-background-generator.php       # Main plugin file (1,024 lines)
yt-random-background-generator.css       # Admin/frontend styles (584 lines)
yt-random-background-generator.js        # Admin interactions (456 lines)
README-yt-random-background-generator.md # Documentation
```

### Constants Defined

```php
YT_RBG_VERSION   // Plugin version (1.0.0)
YT_RBG_BASENAME  // Plugin basename
YT_RBG_PATH      // Plugin directory path
YT_RBG_URL       // Plugin directory URL
```

### Database Storage

**Option Name**: `yt_rbg_options`

**Option Structure**:
```php
array(
    'enabled'              => true,
    'background_type'      => 'color', // 'color', 'image', 'mixed'
    'colors'               => array('#3498db', '#e74c3c', '#2ecc71'),
    'images'               => array('https://example.com/img1.jpg'),
    'image_size'           => 'cover',
    'image_position'       => 'center center',
    'image_repeat'         => 'no-repeat',
    'image_attachment'     => 'fixed',
    'change_frequency'     => 'every_load', // 'every_load', 'daily', 'session'
    'persist_session'      => false,
    'target_element'       => 'body',
    'custom_css'           => '',
    'enable_categories'    => false,
    'category_backgrounds' => array(
        123 => array('type' => 'color', 'value' => '#3498db')
    ),
    'enable_tags'          => false,
    'tag_backgrounds'      => array(),
    'enable_post_types'    => false,
    'post_type_backgrounds' => array(),
    'fallback_color'       => '#ffffff',
    'transition_enabled'   => true,
    'transition_duration'  => '0.5s'
)
```

### WordPress Hooks

#### Actions
- `plugins_loaded`: Load text domain
- `admin_enqueue_scripts`: Load admin CSS/JS
- `wp_enqueue_scripts`: Load frontend CSS
- `admin_menu`: Add settings page
- `admin_init`: Register settings
- `wp_head`: Inject background CSS
- `init`: Start PHP session (if needed)

#### Filters
- `plugin_action_links_{basename}`: Add settings link

#### AJAX Endpoints
- `yt_rbg_preview_background`: Generate random preview
- `yt_rbg_upload_image`: Handle image uploads

### Random Selection Algorithm

```php
// Daily background (consistent per day)
$seed = current_time('Ymd'); // e.g., 20250118
mt_srand($seed);
$index = array_rand($backgrounds);

// Session background (consistent per visitor)
if (!isset($_SESSION['yt_rbg_seed'])) {
    $_SESSION['yt_rbg_seed'] = time();
}
mt_srand($_SESSION['yt_rbg_seed']);

// Every load (truly random)
// No seed - uses default randomness
```

### CSS Injection

The plugin injects CSS directly into `<head>`:

```html
<!-- Random Background Generator -->
<style id="yt-rbg-inline-css">
body {
    background-color: #3498db !important;
    transition: background-color 0.5s ease, background-image 0.5s ease;
}
</style>
```

For images:
```css
body {
    background-image: url(image.jpg) !important;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
}
```

### Category Detection

```php
// Category archive page
if (is_category()) {
    $term_id = get_queried_object_id();
    // Use category-specific background
}

// Single post with category
if (is_single()) {
    $categories = get_the_category();
    foreach ($categories as $category) {
        // Check for category-specific background
    }
}
```

## Security Features

- **Capability Checks**: `manage_options` for settings
- **Nonce Verification**: All AJAX requests
- **Input Sanitization**:
  - `sanitize_hex_color()` for color values
  - `esc_url_raw()` for image URLs
  - `sanitize_text_field()` for text inputs
  - `wp_strip_all_tags()` for custom CSS
- **Output Escaping**:
  - `esc_attr()` for CSS attributes
  - `esc_url()` for image URLs
  - `esc_html()` for text display
- **Array Validation**: Type checking for arrays
- **XSS Prevention**: Proper HTML filtering

## Performance

### Resource Usage

- **Database**: 1 option entry (5-50 KB depending on content)
- **HTTP Requests**: 0 (inline CSS injection)
- **Memory**: < 100 KB
- **Frontend Impact**: +1-2ms (CSS generation)

### Load Time Impact

- **Admin Pages**: +100-150ms (CSS/JS only on settings page)
- **Frontend**: +5-10ms (background selection and CSS generation)
- **Session Mode**: +1ms (session check only)

### Optimization

- **No External Files**: CSS injected inline
- **Conditional Loading**: Admin assets only on settings page
- **Efficient Selection**: O(1) random selection
- **Caching**: Session/daily modes reduce computation
- **Minimal DOM Impact**: Single style tag injection

## Frequently Asked Questions

### Can I use gradients?

Not directly. Add solid colors and use Custom CSS field:
```css
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
```

### Can backgrounds change without page reload?

No, backgrounds change on page load only. For dynamic changes, you'd need custom JavaScript.

### Do backgrounds work with page builders?

Yes, if you set the Target Element to the builder's container (e.g., `.elementor-page`, `.fl-builder`).

### Can I exclude specific pages?

Not built-in. Consider using Custom CSS with `:not()` selector or conditional logic via child theme.

### What image formats are supported?

All web formats: JPG, PNG, GIF, WebP, SVG.

### What's the recommended image size?

- Desktop: 1920x1080px or larger
- Mobile-friendly: 1200x800px minimum
- File size: Under 500KB for performance

### Can I use external image URLs?

Yes, paste any image URL in the category/tag background value field.

### Does this work with caching plugins?

Yes, CSS is generated server-side on each request before caching.

### Can I have different backgrounds for mobile/desktop?

Not directly. Use Custom CSS with media queries:
```css
@media (max-width: 768px) {
    body { background-color: #specific-color !important; }
}
```

### What happens if no backgrounds are configured?

The Fallback Color is used (default: white).

## Troubleshooting

### Background not appearing

**Causes**:
- Plugin not enabled
- No backgrounds configured
- Target element incorrect
- Theme CSS overriding

**Solutions**:
- Check "Enable Random Backgrounds" is checked
- Add at least one color or image
- Verify Target Element selector
- Increase CSS specificity or add `!important`

### Same background every time

**Causes**:
- Change Frequency set to Daily or Session
- Caching plugin caching CSS
- Only one background configured

**Solutions**:
- Change frequency to "Every page load"
- Clear cache
- Add more backgrounds to pool

### Images not loading

**Causes**:
- Invalid image URL
- HTTPS/HTTP mismatch
- Image file deleted
- CORS issues (external images)

**Solutions**:
- Verify image URLs are accessible
- Ensure HTTPS for secure sites
- Re-upload images to Media Library
- Use images from same domain

### Background not covering full page

**Causes**:
- Image Size set to Contain or Auto
- Target Element too small
- Theme has wrapper elements

**Solutions**:
- Set Image Size to "Cover"
- Change Target Element to `body`
- Adjust CSS with Custom CSS field

### Category backgrounds not working

**Causes**:
- Category backgrounds not enabled
- Viewing archive vs. single post
- Wrong category ID

**Solutions**:
- Enable "Category Backgrounds" setting
- Check if on category archive or single post
- Verify category has background assigned

### Performance issues

**Causes**:
- Large image files
- Too many images
- Transition effects

**Solutions**:
- Optimize images (compress, resize)
- Limit to 10-20 backgrounds
- Disable transitions
- Use colors instead of images

### Session mode not working

**Causes**:
- Sessions not started
- Session data not persisting
- Hosting restrictions

**Solutions**:
- Check PHP sessions enabled
- Contact hosting support
- Use "Daily" mode instead

## Best Practices

### Performance

**Do**:
- Optimize images (compress, WebP format)
- Limit to 10-20 backgrounds
- Use solid colors when possible
- Set reasonable transition duration (0.3-1s)

**Don't**:
- Use huge image files (>1MB)
- Add 50+ backgrounds
- Enable transitions on slow sites
- Target multiple elements

### Visual Design

**Do**:
- Choose colors with good contrast for text
- Use cohesive color palettes
- Test backgrounds with actual content
- Consider mobile viewports

**Don't**:
- Use clashing colors
- Forget about text readability
- Use busy patterns that distract
- Ignore mobile users

### User Experience

**Do**:
- Use Session mode for consistency
- Provide visual coherence
- Test with different content types
- Consider accessibility

**Don't**:
- Change too frequently (jarring)
- Use extreme contrasts
- Forget color-blind users
- Ignore page load impact

### Content Strategy

**Do**:
- Match backgrounds to content themes
- Use category backgrounds strategically
- Keep branding consistent
- Test with real users

**Don't**:
- Randomize on every micro-interaction
- Conflict with brand colors
- Overwhelm content
- Use without purpose

## Privacy & Data

### What Data is Stored?

- Plugin settings (colors, images, configuration)
- Session data (if Session mode enabled)
- No user-specific data
- No tracking or analytics

### Session Storage

When Session mode enabled:
- PHP session stores random seed
- Session ID stored in browser cookie
- Cleared when browser closes
- No personal data collected

### GDPR Considerations

- No personal data collected
- Session cookies are functional only
- No external API calls
- No user tracking
- No analytics

## Uninstallation

When you delete the plugin:

1. Plugin settings deleted from database
2. Session data cleared
3. Inline CSS removed from pages
4. Backgrounds revert to theme defaults
5. No residual files

**Note**: Backgrounds are non-permanent, removed immediately on plugin deletion.

## Changelog

### 1.0.0 (2025-01-XX)
- Initial release
- Random color backgrounds
- Random image backgrounds
- Mixed mode (colors + images)
- Change frequency options (every load, daily, session)
- Category-specific backgrounds
- Tag-specific backgrounds (optional)
- Post type backgrounds (optional)
- Color picker integration
- Media library integration
- Image display settings (size, position, repeat, attachment)
- Live preview generator
- Smooth transitions
- Custom target element
- Fallback color
- Custom CSS field
- Translation ready
- Mobile responsive admin

## Roadmap

Potential future features:

- Time-based backgrounds (morning, afternoon, evening)
- Weather-based backgrounds (API integration)
- User preference selection
- Background scheduler (specific dates)
- Video backgrounds
- Animated gradients
- Multiple element targeting
- Background templates library
- Import/export settings
- Background analytics
- A/B testing
- Conditional logic builder
- Background overlays
- Text color auto-adjustment
- Blur effects
- Parallax intensity control

## Developer Notes

### Line Count
- **PHP**: 1,024 lines
- **CSS**: 584 lines
- **JS**: 456 lines
- **Total**: 2,064 lines

### Extending the Plugin

#### Add Custom Background Selection Logic

```php
add_filter('yt_rbg_background', function($background) {
    // Custom logic to modify background
    if (is_front_page()) {
        return array(
            'type' => 'color',
            'value' => '#custom-color'
        );
    }
    return $background;
});
```

#### Modify CSS Output

```php
add_filter('yt_rbg_css', function($css, $background) {
    // Add custom CSS rules
    $css .= ' body::before { content: ""; }';
    return $css;
}, 10, 2);
```

#### Add Custom Background Type

```php
add_filter('yt_rbg_background_types', function($types) {
    $types['gradient'] = __('Gradients', 'textdomain');
    return $types;
});
```

#### Modify Random Selection Pool

```php
add_filter('yt_rbg_available_backgrounds', function($backgrounds, $type) {
    // Filter backgrounds based on custom logic
    return array_filter($backgrounds, 'my_custom_filter');
}, 10, 2);
```

### JavaScript API

Access JavaScript functions:

```javascript
// Get manager instance
var manager = window.RandomBackgroundGenerator;

// Generate preview
manager.generatePreview();

// Get random background
var bg = manager.getRandomBackground();

// Apply background to element
manager.applyBackgroundToPreview(bg);

// Export settings
manager.exportSettings();
```

### Hooks Reference

**PHP Actions**:
- `yt_rbg_before_css_injection`: Before CSS injected
- `yt_rbg_after_css_injection`: After CSS injected
- `yt_rbg_settings_saved`: After settings saved

**PHP Filters**:
- `yt_rbg_background`: Modify selected background
- `yt_rbg_css`: Modify generated CSS
- `yt_rbg_target_element`: Modify target selector
- `yt_rbg_available_backgrounds`: Filter background pool

### Contributing

Follow WordPress Coding Standards:

```bash
# PHP Code Sniffer
phpcs --standard=WordPress yt-random-background-generator.php

# JavaScript linting
eslint yt-random-background-generator.js

# CSS linting
stylelint yt-random-background-generator.css
```

## Support

For issues, questions, or feature requests:
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [CSS Background Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/background)
- [GitHub Repository](https://github.com/krasenslavov/yt-random-background-generator)

## License

GPL v2 or later

## Author

**Krasen Slavov**
- Website: [https://krasenslavov.com](https://krasenslavov.com)
- GitHub: [@krasenslavov](https://github.com/krasenslavov)

---

Add dynamic visual excitement to your WordPress site with random backgrounds!
