<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Direcciones {
	//Inicializa las acciones de Direcciones
	public function __construct() {
		add_filter( 'woocommerce_formatted_address_replacements', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 10, 2 );
        add_filter( 'woocommerce_store_api_checkout_update_order', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 10, 2 );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'apg_nif_formato_direccion_localizacion' ], PHP_INT_MAX );
		add_filter( 'woocommerce_order_formatted_billing_address', [ $this, 'apg_nif_anade_campo_nif_direccion' ], 10, 2 );
		add_filter( 'woocommerce_order_formatted_shipping_address', [ $this, 'apg_nif_anade_campo_nif_direccion' ], 10, 2 );    
        add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_oculta_campo_nif_duplicado' ] );        
    }

    //Reemplaza los nombres de los campos con sus datos
	public function apg_nif_formato_direccion_de_facturacion( $campos, $argumentos ) {
		$campos[ '{nif}' ]            = ( isset( $argumentos[ 'nif' ] ) ) ? $argumentos[ 'nif' ] : '';
		$campos[ '{nif_upper}' ]      = ( isset( $argumentos[ 'nif' ] ) ) ? strtoupper( $argumentos[ 'nif' ] ) : '';
		$campos[ '{phone}' ]          = ( isset( $argumentos[ 'phone' ] ) ) ? $argumentos[ 'phone' ] : '';
		$campos[ '{phone_upper}' ]    = ( isset( $argumentos[ 'phone' ] ) ) ? strtoupper( $argumentos[ 'phone' ] ) : '';
		$campos[ '{email}' ]          = ( isset( $argumentos[ 'email' ] ) ) ? $argumentos[ 'email' ] : '';
		$campos[ '{email_upper}' ]    = ( isset( $argumentos[ 'email' ] ) ) ? strtoupper( $argumentos[ 'email' ] ) : '';

        return $campos;
	}
	
	//Modifica los campos de las direcciones
	public function apg_nif_formato_direccion_localizacion( $direccion ) {
		global $apg_nif_settings;
        
        //Comprueba si no es la página de Finalizar compra ni la de Gracias - Evita problemas con Bloques
        if ( ! is_page( wc_get_page_id( 'checkout' ) ) || ! empty( is_wc_endpoint_url( 'order-received' ) ) ) {
            foreach ( $direccion as $id => $formato ) {
                $direccion[ $id ] = str_replace( "{company}", "{company}\n{nif}", $formato );
            }
        }

        return $direccion;
	}

	//Añade el NIF y el teléfono a la dirección de facturación y envío
    public function apg_nif_anade_campo_nif_direccion( $campos, $pedido ) {
        if ( ! is_array( $campos ) ) {
            return $campos;
        }

        //Detecta si es billing o shipping
        $tipo       = strpos( current_filter(), 'billing' ) !== false ? 'billing' : 'shipping';

        $meta_nif   = $pedido->get_meta( "_{$tipo}_nif", true );
        if ( empty( $meta_nif ) ) {
            $meta_nif   = $pedido->get_meta( "_wc_{$tipo}/apg/nif", true );
        }
        $campos['nif']      = $meta_nif;

        //Email y teléfono
        $campos['email']    = $tipo === 'billing' ? $pedido->get_billing_email() : $pedido->get_meta( "_{$tipo}_email", true );
        $campos['phone']    = $tipo === 'billing' ? $pedido->get_billing_phone() : $pedido->get_shipping_phone();

        return $campos;
    }
    
    //Oculta el campo duplicado en la página Gracias
    public function apg_nif_oculta_campo_nif_duplicado() {
        if ( is_wc_endpoint_url( 'order-received' ) ) {
            wp_register_style( 'apg-nif-hack', false, [], VERSION_apg_nif );
            wp_enqueue_style( 'apg-nif-hack' );
            wp_add_inline_style( 'apg-nif-hack', '.wc-block-components-additional-fields-list { display: none !important; } .woocommerce-customer-details--phone, .woocommerce-customer-details--email { margin: 0; }' );
        }
    }
}
new APG_Campo_NIF_en_Direcciones();
