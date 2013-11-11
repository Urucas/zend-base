form = null;

var kuestyback = {

	eliminarregistro : function( id, module ){

		url = '/barra/' + module + '/eliminar/' + id;
		kuestyback.eliminar( url );
		
	},

	eliminar : function( url, msg ){

		if ( msg == null )
			msg = 'Desea eliminar el registro seleccionado ?';

		ok = confirm( msg );
		
		if( ok ){
//			Si desea eliminar

			document.location.href = url;

		}

	},
	
	validarpass : function(){

		pass	=	$('#password').val();
		repass	=	$('#repassword').val();

		if( pass != '' ){
			
			if( repass == '' ){
				//Si pass no esta vacio y repass si
				$('#msg').html( 'El campo "Repetir Password" no puede estar vacio. Verifique.');
				repass.focus;
				return false;

			} else {
				
				if( pass != repass ){
					//Si pass y repass no estan vacios verifico que sean iguales
					$('#msg').html( 'El campo "Password" y Repetir Password" deben coincidir. Verifique.');
					pass.focus;
					return false;

				}

			}

		}
		
		document.forms[0].submit();
		
	}

}
