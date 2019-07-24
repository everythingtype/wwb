<?php
/**
* Tag Groups Premium
*
* @package     Tag Groups Premium
* @author      Christoph Amthor
* @copyright   2017 Christoph Amthor (@ Chatty Mango, chattymango.com)
* @license     see official vendor website
*
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
*/

if ( ! class_exists( 'TagGroups_Admin_Notice' ) ) {

  class TagGroups_Admin_Notice {

    public function __construct() {
    }

    /**
    * undocumented function summary
    *
    * Undocumented function long description
    *
    * @param string $type One of: error, info
    * @param string $content with HTML
    * @return void
    */
    public static function add( $type, $content )
    {

      $notices = get_option( 'tag_group_admin_notice', array() );

      /**
      * Avoid duplicate entries
      */
      $found = false;

      foreach ( $notices as $notice ) {

        if ( $notice['type'] == $type && $notice['content'] == $content ) {

          $found = true;

          break;

        }

      }

      if ( ! $found ) {

        $notices[] = array(
          'type' => $type,
          'content' => $content
        );

        update_option( 'tag_group_admin_notice', $notices );

      }

    }


    /**
    * Checks if an admin notice is pending and, if necessary, display it
    *
    *
    * @param void
    * @return void
    */
    public static function display() {

      $notices = get_option( 'tag_group_admin_notice', array() );


      if ( ! empty( $notices ) ) {

        $html = '';

        foreach ( $notices as $notice ) {

          if ( 'cache' == $notice['type'] ) {

            $notice['type'] = 'info';

            $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

            $ajax_link = admin_url( 'admin-ajax.php?', $protocol );

            $html .= '
            <script>
            jQuery(document).ready(function(){
              jQuery("#tag_groups_premium_clear_cache").click(function(){
                jQuery("#tag_groups_premium_clear_cache").attr("disabled", "disabled");
                jQuery.ajax({
                  url: "' . $ajax_link . '",
                  data: {
                    action: "tg_ajax_clear_object_cache",
                  },
                  success: function( data ) {
                    jQuery("#tag_groups_premium_clear_cache").replaceWith("<span class=\'dashicons dashicons-yes\'></span>");
                  }
                });
              });
            });
            </script>';

          }

          // wrap the message in <p></p> if not already a complex formatting
          if ( strpos( '<p>', $notice['content'] ) === false ) {

            $notice['content'] = '<p>' . $notice['content'] . '</p>';

          }

          $html .='<div class="notice notice-' . $notice['type'] . ' is-dismissible" style="clear:both;">' .
          $notice['content'] .
          '<div style="clear:both;"></div></div>';

        }

        echo $html;

        update_option( 'tag_group_admin_notice', array() );

      }

    }

  }
}
