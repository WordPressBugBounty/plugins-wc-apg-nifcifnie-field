<?php
/**
 * Validaciones específicas de NIF/CUIT/RUT por país
 * Archivo incluido desde pedido.php
 */

//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Comprueba si el CUIT ingresado es válido (Argentina) - https://github.com/maurozadu/CUIT-Validator/blob/master/libs/cuit_validator.php
//AR
function apg_nif_valida_ar( string $vat ): bool {
    // Limpia guiones, espacios y puntos
    $vat    = preg_replace( '/[^\d]/', '', $vat );

    // Debe tener exactamente 11 dígitos
    if ( strlen( $vat ) !== 11 || !ctype_digit( $vat ) ) {
        return false;
    }

    // Extrae dígitos y calcula verificador
    $mult   = [ 5, 4, 3, 2, 7, 6, 5, 4, 3, 2 ];
    $suma   = 0;
    for ( $i = 0; $i < 10; $i++ ) {
        $suma   += ( int ) $vat[ $i ] * $mult[ $i ];
    }
    $resto          = $suma % 11;
    $verificador    = 11 - $resto;
    if ( $verificador === 11 )$verificador = 0;
    if ( $verificador === 10 )$verificador = 9;

    return ( int ) $vat[ 10 ] === $verificador;
}

// Valida el campo NIF (Austria)
//AT
function apg_nif_valida_at( string $vat ): bool {
    $vat = preg_replace( '/[^0-9A-Z]/', '', strtoupper( $vat ) );
    $vat = str_replace( 'ATU', '', $vat );

    if ( strlen( $vat ) !== 8 || !is_numeric($vat[0]) ) { // Corrected format check
        return false;
    }

    $check_digit = ( int )$vat[ 7 ];
    $vat_number = substr( $vat, 0, 7 ); // Changed variable name for clarity

    $multipliers = [ 1, 2, 1, 2, 1, 2, 1 ];
    $sum = 0;

    for ( $i = 0; $i < 7; $i++ ) {
        $product = ( int )$vat_number[ $i ] * $multipliers[ $i ];
        if ( $product > 9 ) {
            $product = floor( $product / 10 ) + ( $product % 10 );
        }
        $sum += $product;
    }

    $check = 10 - ( $sum + 4 ) % 10;
    if ( $check === 10 ) {
        $check = 0;
    }

    return $check === $check_digit;
}

//Valida el campo NIF (Bélgica)
//BE
function apg_nif_valida_be( string $vat ): bool {
    $vat    = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) === 9 ) {
        $vat    = '0' . $vat;
    }
    if ( strlen( $vat ) !== 10 ) {
        return false;
    }

    $num    = (int)substr( $vat, 0, 8 );
    $check  = (int)substr( $vat, 8, 2 );
    return (97 - ($num % 97)) === $check && ($num % 97) !== 0;
}

//Valida el campo NIF (Bulgaria)
//BG
function apg_nif_valida_bg( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );

    if ( strlen( $vat ) === 9 ) {
        // Persona jurídica
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += (int) $vat[ $i ] * ( $i + 1 );
        }
        $check = $sum % 11;
        if ( $check === 10 ) {
            $sum = 0;
            for ( $i = 0; $i < 8; $i++ ) {
                $sum += (int) $vat[ $i ] * ( $i + 3 );
            }
            $check = $sum % 11;
            if ( $check === 10 ) $check = 0;
        }
        return (int) $vat[8] === $check;
    }

    if ( strlen( $vat ) === 10 ) {
        // Persona física o extranjero
        $month = intval( substr( $vat, 2, 2 ) );
        if ( $month >= 1 && $month <= 12 ) {
            $mult = [2, 4, 8, 5, 10, 9, 7, 3, 6];
        } elseif ( $month >= 21 && $month <= 32 ) {
            $mult = [2, 4, 8, 5, 10, 9, 7, 3, 6];
        } elseif ( $month >= 41 && $month <= 52 ) {
            $mult = [2, 4, 8, 5, 10, 9, 7, 3, 6];
        } else {
            return false;
        }

        $sum = 0;
        for ( $i = 0; $i < 9; $i++ ) {
            $sum += (int) $vat[ $i ] * $mult[ $i ];
        }
        $check = $sum % 11;
        if ( $check === 10 ) $check = 0;

        return (int) $vat[9] === $check;
    }

    return false;
}

//Comprueba si el RUT ingresado es válido (Chile) - https://gist.github.com/punchi/3a5c44e7aa7ac0609ce9e53365572541
//CL
function apg_nif_valida_cl( $vat ) {
    // Eliminar puntos y guión
    $vat    = strtoupper( preg_replace( '/[^0-9K]/', '', $vat ) );
    if ( strlen( $vat ) < 2 ) {
        return false;
    }

    // Separar número y dígito verificador
    $dv     = substr( $vat, -1 );
    $numero = substr( $vat, 0, -1 );

    // Validar que solo haya números
    if ( ! ctype_digit( $numero ) ) {
        return false;
    }

    // Cálculo del dígito verificador
    $suma   = 0;
    $factor = 2;
    for ( $i = strlen( $numero ) - 1; $i >= 0; $i-- ) {
        $suma   += intval( $numero[$i] ) * $factor;
        $factor = $factor == 7 ? 2 : $factor + 1;
    }

    $resto  = $suma % 11;
    $dvr    = 11 - $resto;

    if ( $dvr == 11 ) {
        $dvr    = '0';
    } elseif ( $dvr == 10 ) {
        $dvr    = 'K';
    } else {
        $dvr    = strval( $dvr );
    }

    return $dvr === $dv;
}

//Valida el campo NIF (República Checa)
//CZ
function apg_nif_valida_cz( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $length = strlen( $vat );

    if ( $length !== 8 && $length !== 9 && $length !== 10 ) return false;

    if ( $length === 8 ) {
        $sum = 0;
        for ( $i = 0; $i < 7; $i++ ) {
            $sum += (int) $vat[ $i ] * ( 8 - $i );
        }
        $check = 11 - ( $sum % 11 );
        if ( $check === 10 ) $check = 0;
        if ( $check === 11 ) $check = 1;
        return (int) $vat[7] === $check;
    }

    if ( $length === 9 || $length === 10 ) {
        return ctype_digit( $vat );
    }

    return false;
}

//Valida el campo NIF (Alemania)
//DE
function apg_nif_valida_de( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 9 ) {
		return false;
	}

	$product = 10;
	for ( $i = 0; $i < 8; $i++ ) {
		// Convertir el carácter a un entero restando el valor ASCII de '0'
		$digit = (int) $vat[$i];
		$sum = ($digit + $product) % 10;
		if ($sum === 0) {
			$sum = 10;
		}
		$product = (2 * $sum) % 11;
	}
	$check = 11 - $product;
	if ($check === 10) {
		$check = 0;
	} elseif ($check === 11) {
		$check = 1;
	}

	return (int) $vat[8] === $check;
}

//Valida el campo NIF (Dinamarca)
//DK
function apg_nif_valida_dk( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 8 ) {
        return false;
    }

    $mult = [2, 7, 6, 5, 4, 3, 2, 1];
    $sum = 0;

    for ( $i = 0; $i < 8; $i++ ) {
        $sum += intval( $vat[$i] ) * $mult[$i];
    }

    return ( $sum % 11 ) === 0;
}

//Valida el campo NIF (Estonia)
//EE
function apg_nif_valida_ee( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += intval( $vat[$i] ) * ( $i + 1 );
    }

    $check = $sum % 11;
    if ( $check === 10 ) {
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += intval( $vat[$i] ) * ( $i + 3 );
        }
        $check = $sum % 11;
        if ( $check === 10 ) $check = 0;
    }

    return intval( $vat[8] ) === $check;
}

//Valida el campo NIF/CIF/NIE (España)
//ES
function apg_nif_valida_es( $vat ) {
    $vat_valido = false;
    $vat        = preg_replace( '/[ -,.]/', '', $vat );
    $vat        = str_replace( 'ES', '', $vat );

    for ( $i = 0; $i < 9; $i++ ) {
        $numero[ $i ]   = substr( $vat, $i, 1 );
    }

    if ( ! preg_match( '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $vat ) ) { //No tiene formato válido
        return false;
    }

    if ( preg_match( '/(^[0-9]{8}[A-Z]{1}$)/', $vat ) ) {
        if ( $numero[ 8 ] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( $vat, 0, 8 ) % 23, 1 ) ) { //NIF válido
            $vat_valido = true;
        }
    }

    $suma   = $numero[ 2 ] + $numero[ 4 ] + $numero[ 6 ];
    for ( $i = 1; $i < 8; $i += 2 ) {
        if ( 2 * $numero[ $i ] >= 10 ) {
            $suma   += substr( ( 2 * $numero[ $i ] ), 0, 1 ) + substr( ( 2 * $numero[ $i ] ), 1, 1 );
        } else {
            $suma   += 2 * $numero[ $i ];
        }
    }
    $suma_numero    = 10 - substr( $suma, strlen( $suma ) - 1, 1 );

    if ( preg_match( '/^[KLM]{1}/', $vat ) ) { //NIF especial válido
        if ( $numero[ 8 ] == chr( 64 + $suma_numero ) ) {
            $vat_valido = true;
        }
    }

    if ( preg_match( '/^[ABCDEFGHJNPQRSUVW]{1}/', $vat ) && isset( $numero[ 8 ] ) ) {
        if ( $numero[ 8 ] == chr( 64 + $suma_numero ) || $numero[ 8 ] == substr( $suma_numero, strlen( $suma_numero ) - 1, 1 ) ) { //CIF válido
            $vat_valido = true;
        }
    }

    if ( preg_match( '/^[T]{1}/', $vat ) ) {
        if ( $numero[ 8 ] == preg_match( '/^[T]{1}[A-Z0-9]{8}$/', $vat ) ) { //NIE válido (T)
            $vat_valido = true;
        }
    }

    if ( preg_match( '/^[XYZ]{1}/', $vat ) ) { //NIE válido (XYZ)
        if ( $numero[ 8 ] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( str_replace( [ 'X', 'Y', 'Z' ], [ '0', '1', '2' ], $vat ), 0, 8 ) % 23, 1 ) ) {
            $vat_valido = true;
        }
    }

    return $vat_valido;
}

//Valida el campo NIF (Grecia)
//GR
function apg_nif_valida_gr( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 9 ) {
		return false;
	}
    
	$suma = 0;
	for ( $i = 0; $i < 8; $i++ ) {
		$suma += intval( $vat[ $i ] ) * pow( 2, 8 - $i );
	}
    
	return intval( $vat[8] ) === ( $suma % 11 ) % 10;
}

//Valida el campo NIF (Finlandia)
//FI
function apg_nif_valida_fi( string $vat ): bool {
    $vat    = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 8 ) {
        return false;
    }

    $mult = [ 7, 9, 10, 5, 8, 4, 2 ];
    $suma = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $suma += intval( $vat[ $i ] ) * $mult[ $i ];
    }
    $resto = $suma % 11;
    if ( $resto == 0 ){
        $control = 0;
    } elseif ( $resto == 1 ) {
        return false;
    } else {
        $control = 11 - $resto;
    }
    
    return $control === intval( $vat[7] );
}

//Valida el campo NIF (Francia)
//FR
function apg_nif_valida_fr( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = str_replace( 'FR', '', $vat );

    // Formato 1: 11 dígitos (empresas)
    if ( ctype_digit( $vat ) && strlen( $vat ) === 11 ) {
        $key = substr( $vat, 0, 2 );
        $number = substr( $vat, 2 );
        $computed_key = ( 12 + 3 * ( $number % 97 ) ) % 97;
        return ( int )$key === $computed_key;
    }

    // Formato 2: Letra + 9 dígitos (personas físicas)
    if ( preg_match( '/^[A-HJ-NP-Z][0-9]{9}$/', $vat ) ) {
        return true; // No hay checksum en este formato
    }

    // Formato 3: 2 letras + 9 dígitos (entidades especiales)
    if ( preg_match( '/^[A-HJ-NP-Z]{2}[0-9]{9}$/', $vat ) ) {
        return true;
    }

    return false;
}

//Valida el campo NIF (Croacia)
//HR
function apg_nif_valida_hr( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 11 ) return false;

    $product = 10;
    for ( $i = 0; $i < 10; $i++ ) {
        $sum = ( ( int )$vat[ $i ] + $product ) % 10;
        $sum = ( $sum === 0 ) ? 10 : $sum;
        $product = ( 2 * $sum ) % 11;
    }
    $check = ( 11 - $product ) % 10;

    return ( int )$vat[ 10 ] === $check;
}

//Valida el campo NIF (Hungría)
//HU
function apg_nif_valida_hu( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 8 ) return false;

    $weights = [9, 7, 3, 1, 9, 7, 3];
    $sum = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $sum += (int) $vat[ $i ] * $weights[ $i ];
    }

    $check = $sum % 10;

    return (int) $vat[7] === $check;
}

//Valida el campo NIF (Irlanda)
//IE
function apg_nif_valida_ie(string $vat): bool {
    $vat = strtoupper(preg_replace('/[^A-Z0-9]/', '', $vat));
    $vat = str_replace('IE', '', $vat);

    // Formato 1: 7 dígitos + 1 letra (A-W)
    if (preg_match('/^\d{7}[A-W]$/', $vat)) {
        $weights = [8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (int)$vat[$i] * $weights[$i];
        }
        $check = chr(($sum % 23) + 64); // A=65, B=66, etc.
        return $vat[7] === $check;
    }

    // Formato 2: 1 letra (7-9) + 6 dígitos + 1 letra (A-W)
    if (preg_match('/^[7-9][A-Z*+]\d{5}[A-W]$/', $vat)) {
        return true; // No hay checksum en este formato
    }

    return false;
}

//Valida el campo NIF (Italia)
//IT
function apg_nif_valida_it( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 11 ) {
		return false;
	}
    
	$suma = 0;
	for ( $i = 0; $i < 10; $i++ ) {
		$n = (int) $vat[ $i ];
		if ( $i % 2 === 0 ) {
			$suma += $n;
		} else {
			$n *= 2;
			if ( $n > 9 ) {
				$n -= 9;
			}
			$suma += $n;
		}
	}
	$check = ( 10 - ( $suma % 10 ) ) % 10;

    return ( int )$vat[ 10 ] === $check;
}

//Valida el campo NIF (Lituania)
//LT
function apg_nif_valida_lt( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );

    if ( strlen( $vat ) === 9 ) {
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += intval( $vat[$i] ) * (1 + $i);
        }

        $check = $sum % 11;
        if ( $check === 10 ) {
            $sum = 0;
            for ( $i = 0; $i < 8; $i++ ) {
                $sum += intval( $vat[$i] ) * (1 + (($i + 2) % 9));
            }
            $check = $sum % 11;
            if ( $check === 10 ) $check = 0;
        }

        return intval( $vat[8] ) === $check;
    }

    return false;
}

//Valida el campo NIF (Luxemburgo)
//LU
function apg_nif_valida_lu( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 8 ) {
		return false;
	}
    
	$num = intval( substr( $vat, 0, 6 ) );
	$check = intval( substr( $vat, -2 ) );
    
	return ( $num % 89 ) === $check;
}

// Valida el campo NIF (Letonia)
//LV
function apg_nif_valida_lv( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 11 ) {
		return false;
	}
	
	// Validación especial para personas físicas
	if ( $vat[0] > '3' ) {
		return true;
	}
	
	$suma = 0;
	$mult = [1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
	for ( $i = 0; $i < 10; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}
	
	$check = ( $suma % 11 );
	if ( $check === 4 && $vat[6] == 1 ) {
		$check = 4;
	}
	
	if ( $check === 10 ) {
		$check = 0;
	}
	
	return intval( $vat[10] ) === $check;
}

// Valida el campo NIF (Malta)
//MT
function apg_nif_valida_mt( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 8 ) {
		return false;
	}

	$mult = [3, 4, 6, 7, 8, 9, 10];
	$suma = 0;
	for ( $i = 0; $i < 7; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	$check = $suma % 37;

	return intval( $vat[7] ) === $check;
}

//Valida el campo NIF (Países Bajos)
//NL
function apg_nif_valida_nl( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    if ( ! preg_match( '/^(\d{9})B\d{2}$/', $vat, $matches ) ) {
        return false;
    }

    $base = $matches[ 1 ];
    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += ( int )$base[ $i ] * ( 9 - $i );
    }

    $check = $sum % 11;
    if ( $check === 10 ) {
        $check = 0;
    }

    return ( int )$base[ 8 ] === $check;
}

// Valida el campo NIF (Noruega)
// NO
function apg_nif_valida_no( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $weights = [ 3, 2, 7, 6, 5, 4, 3, 2 ];
    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += (int) $vat[ $i ] * $weights[ $i ];
    }

    $check = 11 - ($sum % 11);
    if ($check === 11) $check = 0;
    if ($check === 10) return false; // 10 no es un dígito de control válido

    return (int) $vat[8] === $check;
}

// Valida el campo NIF (Polonia)
//PL
function apg_nif_valida_pl( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 10 ) {
		return false;
	}

	$mult = [6, 5, 7, 2, 3, 4, 5, 6, 7];
	$suma = 0;
	for ( $i = 0; $i < 9; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	$check = $suma % 11;
	if ( $check === 10 ) {
		return false;
	}

	return intval( $vat[9] ) === $check;
}

//Valida el campo NIF (Portugal)
//PT
function apg_nif_valida_pt( string $vat ): bool {
    // Acepta 1, 2, 3, 5, 6, 8, 9 como iniciales válidas
    if ( ! preg_match( '/^[1235689][0-9]{8}$/', $vat ) ) {
        return false;
    }

    $suma = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $suma += intval( $vat[ $i ] ) * ( 9 - $i );
    }

    $resto  = $suma % 11;
    $digito = ( $resto < 2 ) ? 0 : 11 - $resto;

    return intval( $vat[8] ) === $digito;
}

// Valida el campo NIF (Rumanía)
//RO
function apg_nif_valida_ro( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $length = strlen( $vat );

    if ( $length < 2 || $length > 10 ) return false;

    $vat = str_pad( $vat, 10, '0', STR_PAD_LEFT );
    $mult = [ 7, 5, 3, 2, 1, 7, 5, 3, 2 ];
    $sum = 0;

    for ( $i = 0; $i < 9; $i++ ) {
        $sum += ( int )$vat[ $i ] * $mult[ $i ];
    }

    $check = ( $sum * 10 ) % 11;
    if ( $check === 10 )$check = 0;

    return ( int )$vat[ 9 ] === $check;
}

//Valida el campo NIF (Suecia)
//SE
function apg_nif_valida_se( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 12 || substr( $vat, -2 ) !== '01' ) {
		return false;
	}
    
	$num = substr( $vat, 0, 10 );
	$suma = 0;
	for ( $i = 0; $i < 10; $i++ ) {
		$tmp = intval( $num[ $i ] ) * ( ( $i % 2 ) ? 1 : 2 );
		if ( $tmp > 9 ) {
			$tmp -= 9;
		}
		$suma += $tmp;
	}
    
    return ( $suma % 10 ) === 0;
}

// Valida el campo NIF (Eslovenia)
//SI
function apg_nif_valida_si( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 8 ) {
		return false;
	}

	$mult = [8, 7, 6, 5, 4, 3, 2];
	$suma = 0;
	for ( $i = 0; $i < 7; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	$check = 11 - ( $suma % 11 );
	if ( $check === 10 ) {
		return false;
	} elseif ( $check === 11 ) {
		$check = 0;
	}

	return intval( $vat[7] ) === $check;
}

// Valida el campo NIF (Eslovaquia)
//SK
function apg_nif_valida_sk( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 10 ) {
		return false;
	}

	return intval( $vat ) % 11 === 0;
}

/**
 * Valida número VAT  mediante expresiones regulares según país.
 * No valida checksum, solo estructura.
 * Basado en JS validator de John Gardner: http://www.braemoor.co.uk/software/vat.shtml y https://github.com/mnestorov/regex-patterns
 *
 * @param string $pais Código ISO 2 del país (ej: ES, DE, FR...)
 * @param string $vat_number Número VAT ya normalizado
 * @return bool
 */
function apg_nif_valida_regex( string $pais, string $vat_number ): bool {
    switch ( $pais ) {
        case 'AL': //Albania 
            return ( bool ) preg_match( '/^(AL)?J(\d{8}[A-Z])$/', $vat_number );
        case 'AT': //Austria 
            return ( bool ) preg_match( '/^(AT)?U(\d{8})$/', $vat_number );
        case 'AX': //Islas de Åland
            return ( bool ) preg_match( '/^(FI)?|(AX)?(\d{8})$/', $vat_number );
        case 'BE': //Bélgica 
            return ( bool ) preg_match( '/(BE)?(0?\d{9})$/', $vat_number );
        case 'BG': //Bulgaria 
            return ( bool ) preg_match( '/(BG)?(\d{9,10})$/', $vat_number );
        case 'BY': //Bielorusia 
            return ( bool ) preg_match( '/(BY)?(\d{9})$/', $vat_number );
        case 'CH': //Suiza 
            return ( bool ) preg_match( '/(CHE)?(\d{9})(MWST)?|(TVA)?|(IVA)?$/', $vat_number );
        case 'CY': //Chipre 
            return ( bool ) preg_match( '/^(CY)?([0-5|9]\d{7}[A-Z])$/', $vat_number );
        case 'CZ': //República Checa
            return ( bool ) preg_match( '/^(CZ)?(\d{8,10})(\d{3})?$/', $vat_number );
        case 'DE': //Alemania 
            return ( bool ) preg_match( '/^(DE)?([1-9]\d{8,9})/', $vat_number );
        case 'DK': //Dinamarca 
            return ( bool ) preg_match( '/^(DK)?(\d{8})$/', $vat_number );
        case 'EE': //Estonia 
            return ( bool ) preg_match( '/^(EE)?(10\d{7,9})$/', $vat_number );
        case 'ES': //España 
            return ( bool ) preg_match( '/^(ES)?([A-Z]\d{8})$/', $vat_number ) ||
                preg_match( '/^(ES)?([A-H|N-S|W]\d{7}[A-J])$/', $vat_number ) ||
                preg_match( '/^(ES)?([0-9|Y|Z]\d{7}[A-Z])$/', $vat_number ) ||
                preg_match( '/^(ES)?([K|L|M|X]\d{7}[A-Z])$/', $vat_number );
        case 'EU': //Unión Europea 
            return ( bool ) preg_match( '/^(EU)?(\d{9})$/', $vat_number );
        case 'FI': //Finlandia 
            return ( bool ) preg_match( '/^(FI)?(\d{8})$/', $vat_number );
        case 'FO': //Islas Feroe
            return ( bool ) preg_match( '/^(FO)?(\d{6})$/', $vat_number );
        case 'FR': //Francia 
            return ( bool ) preg_match( '/^(FR)?(\d{11})$/', $vat_number ) ||
                preg_match( '/^(FR)?([(A-H)|(J-N)|(P-Z)]\d{10})$/', $vat_number ) ||
                preg_match( '/^(FR)?(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/', $vat_number ) ||
                preg_match( '/^(FR)?([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $vat_number );
        case 'GB': //Gran Bretaña 
            return ( bool ) preg_match( '/^(GB)?(\d{9})$/', $vat_number ) ||
                preg_match( '/^(GB)?(\d{12})$/', $vat_number ) ||
                preg_match( '/^(GB)?(GD\d{3})$/', $vat_number ) ||
                preg_match( '/^(GB)?(HA\d{3})$/', $vat_number );
        case 'GR': //Grecia
            return ( bool ) preg_match( '/^(GR)?(\d{8,9})$/', $vat_number ) ||
                preg_match( '/^(EL)?(\d{9})$/', $vat_number );
        case 'HR': //Croacia 
            return ( bool ) preg_match( '/^(HR)?(\d{11})$/', $vat_number );
        case 'HU': //Hungría 
            return ( bool ) preg_match( '/^(HU)?(\d{8})$/', $vat_number );
        case 'IE': //Irlanda 
            return ( bool ) preg_match( '/^(IE)?(\d{7}[A-W])$/', $vat_number ) ||
                preg_match( '/^(IE)?([7-9][A-Z\*\+)]\d{5}[A-W])$/', $vat_number ) ||
                preg_match( '/^(IE)?(\d{7}[A-W][AH])$/', $vat_number );
        case 'IS': //Islandia 
            return ( bool ) preg_match( '/^(IS)?(\d{5,6})$/', $vat_number );
        case 'IT': //Italia 
            return ( bool ) preg_match( '/^(IT)?(\d{11})$/', $vat_number );
        case 'LI': //Liechtenstein 
            return ( bool ) preg_match( '/^(LI)?(\d{5})$/', $vat_number );
        case 'LT': //Lituania 
            return ( bool ) preg_match( '/^(LT)?(\d{9}|\d{12})$/', $vat_number );
        case 'LU': //Luxemburgo 
            return ( bool ) preg_match( '/^(LU)?(\d{8})$/', $vat_number );
        case 'LV': //Letonia 
            return ( bool ) preg_match( '/^(LV)?(\d{11})$/', $vat_number );
        case 'MC': //Mónaco 
            return ( bool ) preg_match( '/^(FR)?(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/', $vat_number ) ||
                preg_match( '/^(FR)?([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $vat_number );
        case 'MD': //Moldavia 
            return ( bool ) preg_match( '/^(MD)?(\d{8})$/', $vat_number );
        case 'ME': //Montenegro 
            return ( bool ) preg_match( '/^(ME)?(\d{8})$/', $vat_number );
        case 'MK': //Macedonia del Norte 
            return ( bool ) preg_match( '/^(MK)?(\d{13})$/', $vat_number );
        case 'MT': //Malta 
            return ( bool ) preg_match( '/^(MT)?([1-9]\d{7,8})$/', $vat_number );
        case 'NL': //Países Bajos 
            return ( bool ) preg_match( '/^(NL)?(\d{9})B\d{2}$/', $vat_number );
        case 'NO': //Noruega 
            return ( bool ) preg_match( '/^(NO)?(\d{9})(MVA)?$/', $vat_number );
        case 'PL': //Polonia 
            return ( bool ) preg_match( '/^(PL)?(\d{10})$/', $vat_number );
        case 'PT': //Portugal 
            return ( bool ) preg_match( '/^(PT)?(\d{9})$/', $vat_number );
        case 'RO': //Rumanía 
            return ( bool ) preg_match( '/^(RO)?([1-9]\d{2,10})$/', $vat_number );
        case 'RS': //Serbia 
            return ( bool ) preg_match( '/^(RS)?(\d{9})$/', $vat_number );
        case 'SE': //Suecia 
            return ( bool ) preg_match( '/^(SE)?(\d{10}01)$/', $vat_number );
        case 'SI': //Eslovenia 
            return ( bool ) preg_match( '/^(SI)?([1-9]\d{7,8})$/', $vat_number );
        case 'SK': //República Eslovaca
            return ( bool ) preg_match( '/^(SK)?([1-9]\d[(2-4)|(6-9)]\d{7})$/', $vat_number );
        case 'SM': // San Marino
            return ( bool ) preg_match( '/^(SM)?(\d{5})$/', $vat_number );
        case 'UA': // Ucrania
            return ( bool ) preg_match( '/^(UA)?(\d{12})$/', $vat_number );
        default:
            return false;
    }
}
