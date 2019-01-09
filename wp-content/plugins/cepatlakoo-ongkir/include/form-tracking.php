    <form action="" method="POST" id="tracking-form">
        <!-- <div class="form-group">
            <label for="cl-tracking-order"><?php echo esc_html('Order ID', 'cepatlakoo'); ?></label>
            <input type="text" name="order_id" class="form-control" id="cl-tracking-order" aria-describedby="cl-tracking-order-help" placeholder="<?php echo esc_html('Enter Order ID', 'cepatlakoo'); ?>">
            <small id="cl-tracking-order-help" class="form-text text-muted"><?php echo esc_html('Masukkan Order ID untuk melihat progress pengiriman', 'cepatlakoo'); ?></small>
        </div>
        <p>OR<hr></p> -->
        <?php
            $options    = get_option( 'woocommerce_cl_ongkir_shipping_settings' );
            $type_acc   = get_option('woocommerce_type_account');
            $couriers   = $options['courier'];

            if ( $couriers == null || ( $type_acc == 'starter' && in_array( "all", $couriers ) ) ) {
                $couriers = ['jne', 'tiki', 'pos'];
            } elseif ( in_array( "all", $couriers ) && $type_acc == 'basic' ) {
                $couriers = ['jne', 'tiki', 'pos', 'pcp', 'rpx'];
            } elseif ( in_array( "all", $couriers ) && $type_acc == 'pro' ) {
                $couriers = ['jne', 'pos', 'tiki', 'wahana', 'jnt', 'rpx', 'sap', 'sicepat', 'pcp', 'jet', 'dse', 'first', 'ninja', 'lion', 'idl' ];
            }
        ?>
        <div class="form-group">
            <label for="cl-tracking-courier"><?php echo esc_html('Courier', 'cepatlakoo'); ?></label>
            <select name="courier" class="form-control" id="cl-tracking-courier" aria-describedby="cl-tracking-courier-help" required>
            <?php foreach($couriers as $courier) : ?>
                <option value="<?php echo $courier; ?>"><?php echo strtoupper($courier); ?></option>
            <?php endforeach; ?>
            </select>
            <small id="cl-tracking-courier-help" class="form-text text-muted"><?php echo esc_html('Pilih Courier', 'cepatlakoo'); ?></small>
        </div>
        <div class="form-group">
            <label for="cl-tracking-resi"><?php echo esc_html('Nomor Resi', 'cepatlakoo'); ?></label>
            <input type="text" name="code" class="form-control" id="cl-tracking-resi" aria-describedby="cl-tracking-resi-help" placeholder="<?php echo esc_html('Enter Resi', 'cepatlakoo'); ?>"  required>
            <small id="cl-tracking-order-help" class="form-text text-muted"><?php echo esc_html('Masukkan nomor resi untuk melihat progress pengiriman', 'cepatlakoo'); ?></small>
        </div>
        <input type="hidden" name="action" value="cl_do_tracking">
        <button type="submit" class="button btn btn-default"><?php echo esc_html('Submit', 'cepatlakoo'); ?></button>
    </form>
<div class="break"></div>