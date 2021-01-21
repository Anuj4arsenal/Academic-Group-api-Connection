<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$downloads = WC()->customer->get_downloadable_products();
$downloads_fltr = array();
foreach ($downloads as $download) {
    $product_id = $download['product_id'];
    $downloads_fltr[$product_id] = $downloads_fltr[$product_id] ? $downloads_fltr[$product_id] : array();
    $download['enabled'] = 0;
    $start_date = get_post_meta($product_id, 'ag_start_dw_date', true);
    $end_date = get_post_meta($product_id, 'ag_end_dw_date', true);
    $c_date = strtotime(current_time("Y-m-d H:i"));
    $start_date = strtotime($start_date);
    $end_date = strtotime($end_date);
    //  echo '<';
    //  echo $c_date;
    //  echo '|';
    //  echo $start_date;
    //  echo '|';
    //  echo $end_date;
    //  echo '>';
    //  echo current_time("Y-m-d h:i");
    $download['start_date'] = get_post_meta($product_id, 'ag_start_dw_date', true);
    if ($start_date && $end_date) {
        if ($c_date > $start_date && $c_date < $end_date) {
            $download['enabled'] = 1;
        }
    } else if ($start_date) {
        if ($c_date > $start_date) {
            $download['enabled'] = 1;
        }
    } else if ($end_date) {
        if ($c_date < $end_date) {
            $download['enabled'] = 1;
        }
    } else {
        $download['enabled'] = 1;
    }
    array_push($downloads_fltr[$product_id], $download);
    $details_page = isset($_GET['product_filter']);
}
?>
    <div class="woocommerce-MyAccount-content" style="width: 100%;">
        <div class="woocommerce-notices-wrapper"></div>
        <p>
            <?php
            printf(
            /* translators: 1: user display name 2: logout url */
                __( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ),
                '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
                esc_url( wc_logout_url() )
            );
            ?>
        </p>

        <p>
            For your order details please <a target="_blank" href="https://database.academicgroup.com.au" >Click here </a>.
        </p>
        <?php if($downloads_fltr && !$details_page) { ?>
        <div class="cstm-tbl">
            <h3>Exam Papers</h3>
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($downloads_fltr as $product_id=>$download) {?>
                <tr>
                    <td><a href="<?php echo home_url() ?>/my-account?product_filter=<?php echo $product_id ?>"><?php echo ($download[0]['product_name']) ?></a></td>
                    <td><?php echo ($download[0]['enabled'] == 1 ? 'Active' : 'Inactive') ?></td>
                    <td><a href="<?php echo home_url() ?>/my-account?product_filter=<?php echo $product_id ?>" class="btn btn-blue"><i class="fa fa-download"> </i> View Download</a></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
<?php }
else if($downloads_fltr && $details_page){
    $product_id = $_GET['product_filter'];
    ?>
    <div class="cstm-tbl">
        <h4><?php echo get_the_title($product_id);?> <span style="font-size: 14px;float: right;"><a href="<?php echo home_url() ?>/my-account"><< Back</a></spanstyle></h4>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($downloads_fltr[$product_id] as $download) {?>
                <tr>
                    <td><a href=""><?php echo ($download['download_name']) ?></a></td>
                    <td><?php echo ($download['enabled'] == 1 ? 'Open' : 'Closed') ?></td>
                    <td><a href="<?php echo ($download['enabled'] == 1 ? $download['download_url'] : "") ?>" class=" <?php echo ($download['enabled'] == 1 ? '' : 'disabled') ?> btn btn-blue"><i class="fa fa-download"> </i> <?php echo ($download['enabled'] == 1 ? "Download" : "Available after ".$download['start_date']) ?></a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php }?>
    </div>
    <style>
        .cstm-tbl thead th {
            border-bottom: 1px solid #333 !important;
            border-left: 1px solid #333 !important;
            text-transform: uppercase;
        }
    </style>
<?php
if(!$downloads_fltr){ ?>
    <script>window.location = "https://database.academicgroup.com.au"</script>
    <?php
}
?>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
