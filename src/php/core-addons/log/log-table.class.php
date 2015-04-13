<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

require_once(CUAR_INCLUDES_DIR . '/core-classes/Content/list-table.class.php');

/**
 * Class CUAR_LogTable
 *
 * List logs on the admin side
 *
 * @link http://plugins.svn.wordpress.org/custom-list-table-example/tags/1.3/list-table-example.php
 */
class CUAR_LogTable extends CUAR_ListTable
{

    public $content_types = array();

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     *
     * @param CUAR_Plugin $plugin
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin,
            array(
                'singular' => __('Log', 'cuar'),
                'plural'   => __('Logs', 'cuar'),
                'ajax'     => false
            ),
            admin_url('admin.php?page=wpca-logs'),
            'CUAR_LogEvent');

        $this->content_types = array_merge($plugin->get_content_types(), $plugin->get_container_types());
        $this->displayable_meta = apply_filters('cuar/core/log/table-displayable-meta', array());
    }

    /**
     * Read the parameters from the query and store them for later use
     */
    protected function parse_parameters()
    {
        $this->parameters['status'] = isset($_GET['status']) ? $_GET['status'] : 'any';
        $this->parameters['related-object'] = isset($_GET['related-object']) ? $_GET['related-object'] : -1;
        $this->parameters['event-type'] = isset($_GET['event-type']) ? $_GET['event-type'] : 0;
        $this->parameters['start-date'] = isset($_GET['start-date']) ? sanitize_text_field($_GET['start-date']) : null;
        $this->parameters['end-date'] = isset($_GET['end-date']) ? sanitize_text_field($_GET['end-date']) : null;
    }

    /**
     * Get the query parameters
     * @return array
     */
    protected function get_query_args()
    {
        $logger = $this->plugin->get_logger();
        $args = $logger->build_query_args($this->parameters['related-object'],
            $this->parameters['event-type'],
            null, -1, 1);

        $args['date_query'] = array();

        $start_date = $this->parameters['start-date'];
        if ($start_date != null)
        {
            $start_tokens = explode('/', $start_date);
            if (count($start_tokens) != 3)
            {
                $start_date = null;
            }
            else
            {
                $args['date_query'][] =
                    array(
                        'after'     => array(
                            'year'  => $start_tokens[2],
                            'month' => $start_tokens[1],
                            'day'   => $start_tokens[0],
                        ),
                        'inclusive' => true
                    );
            }
        }

        $end_date = $this->parameters['end-date'];
        if ($end_date != null)
        {
            $end_tokens = explode('/', $end_date);
            if (count($end_tokens) != 3)
            {
                $end_date = null;
            }
            else
            {
                $args['date_query'][] =
                    array(
                        'before'    => array(
                            'year'  => $end_tokens[2],
                            'month' => $end_tokens[1],
                            'day'   => $end_tokens[0],
                        ),
                        'inclusive' => true,
                    );
            }
        }

        return $args;
    }

    /*------- COLUMNS ------------------------------------------------------------------------------------------------*/

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $columns = array_merge(parent::get_columns(), array(
            'log_timestamp' => __('Date', 'cuar'),
            'log_event'     => __('Event', 'cuar'),
            'log_object'    => __('Object', 'cuar'),
            'log_user'      => __('User', 'cuar'),
            'log_extra'     => __('Extra info', 'cuar'),
        ));

        return $columns;
    }

    public function column_log_timestamp($item)
    {
        $m_time = $item->get_post()->post_date;
        $h_time = mysql2date(__('Y/m/d - g:i:s'), $m_time);

        return $h_time;
    }

    public function column_log_object($item)
    {
        $rel_object_id = $item->get_post()->post_parent;
        $rel_object_type = $item->related_object_type;
        $obj_link_text = isset($this->content_types[$rel_object_type])
            ? $this->content_types[$rel_object_type]['label-singular']
            : $rel_object_type;
        $obj_link_text .= ' ' . $rel_object_id;

        return sprintf('<a href="%1$s" title="Title: %2$s">%3$s</a>',
            admin_url('edit.php?post_type=' . $rel_object_type . '&post_id=' . $rel_object_id),
            esc_attr(get_the_title($rel_object_id)),
            $obj_link_text);
    }

    public function column_log_user($item)
    {
        $user_id = isset($item->user_id) ? $item->user_id : 0;
        if ($user_id == 0)
        {
            return '';
        }

        $user = get_userdata($user_id);

        return sprintf('<span title="%4$s" class="cuar-btn-xs ip">IP</span> <a href="%1$s" title="Profile of %2$s" class="cuar-btn-xs user">%3$s</a>',
            admin_url('user-edit.php?user_id=' . $user_id),
            esc_attr($user->display_name),
            $user->user_login,
            $item->ip);
    }

    public function column_log_event($item)
    {
        $type = $item->get_type();
        $logger = $this->plugin->get_logger();
        $types = $logger->get_valid_event_types();

        return isset($types[$type]) ? $types[$type] : 'Unknown';
    }

    public function column_log_extra($item)
    {
        $fields = array();
        $exclude = array('user_id', 'ip');

        foreach ($this->displayable_meta as $key)
        {
            if (in_array($key, $exclude))
            {
                continue;
            }
            if (isset($item->$key))
            {
                $meta = apply_filters('cuar/core/log/table-meta-pill-descriptor', array(
                    'title' => $item->$key,
                    'value' => $key,
                    'link'  => ''
                ), $key, $item);

                if (empty($meta['link']))
                {
                    $fields[] = sprintf('<span title="%1$s" class="cuar-btn-xs %3$s">%2$s</span>', $meta['title'],
                        esc_attr($meta['value']), $key);
                }
                else
                {
                    $fields[] = sprintf('<a href="%4$s" title="%1$s" class="cuar-btn-xs %3$s">%2$s</a>', $meta['title'],
                        esc_attr($meta['value']), $key, esc_attr($meta['link']));
                }
            }
        }

        return implode(' ', $fields);
    }

    /*------- BULK ACTIONS -------------------------------------------------------------------------------------------*/

    /**
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'cuar')
        );

        return $actions;
    }

    /**
     * Execute a bulk action on a single post
     *
     * @param string $action
     * @param int    $post_id
     */
    protected function execute_action($action, $post_id)
    {
        if ('delete' === $action)
        {
            if ( !current_user_can('delete_post', $post_id))
            {
                wp_die(__('You are not allowed to delete this item.'));
            }

            wp_delete_post($post_id, true);
        }
    }

}