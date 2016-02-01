<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.1.0 =-
 * - Updated markup
 * - Normalized the extra class filter name
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
$is_author = get_the_author_meta('ID') == get_current_user_id();
if ($is_author)
{
    $subtitle_popup = __('You published this page', 'cuar');
    $subtitle = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
}
else
{
    $subtitle_popup = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
    $subtitle = sprintf(__('Published by %s', 'cuar'), get_the_author_meta('display_name'));
}

$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());
?>

<tr>
    <td class="cuar-title">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>"><?php the_title(); ?></a>
    </td>
    <td class="cuar-owner">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($subtitle_popup); ?>"><?php echo $subtitle; ?></a>
    </td>
</tr>