<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://ThinkDualBrain.com
 * @since      1.0.0
 *
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/admin/partials
 */

$post_id = $_REQUEST['post_id'];
$order_id = $_REQUEST['order_id'];
$product_id = $_REQUEST['product_id'];
$wp_content = $_REQUEST['content'];
$image = $_REQUEST['image'];
$count = $_REQUEST['count'];

$url = "/wp-admin/admin.php?".http_build_query(
    array(
		'post_id' => $post_id,
		'wineshop_label_action' => 'print_pdf',
	)
);

//$dir = dirname(__FILE__);
//$path = "/wp-content/plugins/wineshop-label-designer/admin";
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>Print Labels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="<?php echo $wp_content; ?>/plugins/wineshop-label-designer/admin/css/wineshop-label-designer-admin.css" />

    <style type="text/css">
        LABEL {

        }
        :checked + label {
            background-image: url('<?php echo $image; ?>');
        }
    </style>
</head>
<body>


<div class="container print-pdf-container">
    <form id="print_pdf_form_<?php echo $post_id; ?>" action="/wp-admin/admin.php" method="GET" target="_blank" class="print-pdf-form">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <input type="hidden" name="wineshop_label_action" value="print_pdf">
        <table cellspacing="40" cellpadding="0">
            <tr>
                <td rowspan="2">
                    <table class="label-table" cellpadding="0" cellspacing="2">
                        <caption>First Page Availability</caption>
                        <tr>
                            <td><input type="checkbox" id="use_slot_1" name="slots[]" value="1" checked>
                                <label for="use_slot_1"><img src="<?php echo $wp_content; ?>/plugins/wineshop-label-designer/admin/images/label-placeholder.png" /></label></td>
                            <td><input type="checkbox" id="use_slot_2" name="slots[]" value="2" checked>
                                <label for="use_slot_2"><img src="<?php echo $wp_content; ?>/plugins/wineshop-label-designer/admin/images/label-placeholder.png" /></label></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" id="use_slot_3" name="slots[]" value="3" checked>
                                <label for="use_slot_3"><img src="<?php echo $wp_content; ?>/plugins/wineshop-label-designer/admin/images/label-placeholder.png" /></label>
                            </td>
                            <td><input type="checkbox" id="use_slot_4" name="slots[]" value="4" checked>
                                <label for="use_slot_4"><img src="<?php echo $wp_content; ?>/plugins/wineshop-label-designer/admin/images/label-placeholder.png" /></label></td>
                        </tr>

                    </table>

                </td>
                <td id="print-count-row">
                    <label for="print-count">Labels to Print:</label>
                    <input id="print-count" name="count" type="number" value="<?php echo $count; ?>" class="small">
                </td>
            </tr>
            <tr>
                <td id="submit-row">
                    <input type="submit" value="Print" class="btn">
                </td>
            </tr>
        </table>
    </form>
</div><!-- container -->

</body>
</html>
