<?php
/*
Plugin Name: YS WP Taxonomy Exporter For WP Taxonomy Import
Version: 1.0.3
Author: yosiakatsuki
Author URI: https://yosiakatsuki.net/
Plugin URI:
Description: WP Taxonomy Import でタクソノミーをインポートするためのテキストを出力するためのプラグイン
*/

/**
 * メニュー追加
 *
 * @return void
 */
function yswpte4ti_admin_menu() {
	add_options_page(
		'YS WP Taxonomy Exporter For Taxonomy Import',
		'YS WP Taxonomy Exporter For Taxonomy Import',
		'manage_options',
		'ys-wp-te4ti',
		'yswpte4ti_options_page'
	);
}
add_action( 'admin_menu', 'yswpte4ti_admin_menu' );

/**
 * メニューページ
 *
 * @return void
 */
function yswpte4ti_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'yswpte4ti' ) );
	}
	$target_taxonomy = isset( $_POST['target_taxonomy'] ) ? $_POST['target_taxonomy'] : '';
	$delimiter = isset( $_POST['delimiter'] ) ? $_POST['delimiter'] : '';
	$taxlist = yswpte4ti_get_tax_list( $target_taxonomy, $delimiter );
	?>
<div class="wrap">
	<h2>YS WP Taxonomy Exporter For Taxonomy Import</h2>
	<form method="post" action="">
	<?php
		settings_fields( 'yswpte4ti_settings' );
		do_settings_sections( 'yswpte4ti_settings' );
	?>
	<div class="inside">
		<h3>デリミタ</h3>
		<p>（デフォルトのデリミタ: $ ）<input type="text" id="delimiter" name="delimiter" maxlength="2" size="2" value="$" placeholder="$" /></p>
		<h3>タクソノミー</h3>
		<select name="target_taxonomy" >
		<?php
		//タクソノミーを取得
		$custom_taxonomies = get_taxonomies( array(), "objects" );
		foreach ( $custom_taxonomies as $key => $taxonomy ) :
		?>
		<option value="<?php echo $taxonomy->name ?>" <?php echo selected( $taxonomy->name, $target_taxonomy ) ?>><?php echo $taxonomy->name ?></option>
		<?php endforeach; ?>
		</select><br>
		<h3>結果</h3>
		<textarea id="taxlist" name="taxlist" rows="20" style="width: 100%;"><?php echo $taxlist; ?></textarea>
	</div>
	<?php submit_button('実行'); ?>
	</form>
	</div><!-- /.warp -->
	<?php
}

/**
 * 一覧作成
 *
 * @param string $target_taxonomy タクソノミー.
 * @param string $delimiter デリミタ.
 * @return string
 */
function yswpte4ti_get_tax_list( $target_taxonomy , $delimiter ) {
	if ( ! $target_taxonomy ) {
		return '';
	}
	return yswpte4ti_get_tax_children( $target_taxonomy, $delimiter );
}

/**
 * 一覧作成（再帰）
 *
 * @param string $target_taxonomy タクソノミー.
 * @param string $delimiter デリミタ.
 * @param integer $parent_id 親タクソノミーID.
 * @param string $parent_text ここまでの親情報.
 * @return string
 */
function yswpte4ti_get_tax_children( $target_taxonomy, $delimiter, $parent_id = 0, $parent_text = '' ) {
	$terms = get_terms(
		$target_taxonomy,
		array(
			'hide_empty' => false,
			'parent' => $parent_id
			)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	if ( $parent_text ) {
		$parent_text = $parent_text . '->';
	}
	foreach ( $terms as $term ) {
		$text = $parent_text . $term->name . $delimiter . urldecode( $term->slug );
		$result .= $text . "\n";
		$children = yswpte4ti_get_tax_children( $target_taxonomy, $delimiter, (int)$term->term_id, $text );
		if ( $children ) {
			$result .= $children;
		}
	}
	return $result;
}
