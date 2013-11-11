var _map, lastInfoWindow, lastMarker;
function _kuesty() {

	this.loadUser = function(id, user) {
	
		document.location.href = "/user/id/"+id+"/user/"+user;
	}

	this.createSearchMap = function(lat, lng) {
	
		var lat = lat;
		var lng = lng;
		
		var centerLatlng = new google.maps.LatLng(lat, lng);

        var myOptions = {
               	zoom: 15,
               	center: centerLatlng,
        		panControl: true,
				overviewMapControl:false,
				rotateControl:false,
				mapTypeControl:false,
               	mapTypeId: google.maps.MapTypeId.ROADMAP
         };

        _map = new google.maps.Map(
            document.getElementById("map_container"),
            myOptions
        );

		//marco mi posicion
		var myLatlng = new google.maps.LatLng(lat, lng);
        var myMarker = new google.maps.Marker({
            position: myLatlng,
            map: _map,
            id : 0,
            icon: '/resources/images/map_pin_me.png',
            flat: true
        });

		_markers = [];
		
		google.maps.event.addListener(_map, 'mouseup', function(event) {
			//myMarker.setPosition(event.latLng);

			kuesty.getNearby(event.latLng.lat(), event.latLng.lng(), function(data) {

				var image = '/resources/images/map_pin_off.png';
				_map.panTo(event.latLng);

				for(var i=0;i<_markers.length;i++){
					var marker = _markers[i];
					marker.setMap(null);
				}
				var locales = data.locales;
				for(var i=0; i< locales.length;i++) {
					var lugar = locales[i];
					var markerPos = new google.maps.LatLng(lugar.latitud, lugar.longitud);
            		var marker = new google.maps.Marker({
                		position: markerPos,
	                	map: _map,
		                icon:image
					});
					kuesty.setMapInfoWindow(marker, lugar);
					_markers.push(marker);

				};
				$("#nearbyList").html(data.html);
			});
		});

		kuesty.getNearby(lat, lng, function(data) {

				var image = '/resources/images/map_pin_off.png';
//				_map.panTo(event.latLng);

				for(var i=0;i<_markers.length;i++){
					var marker = _markers[i];
					marker.setMap(null);
				}
				var locales = data.locales;
				for(var i=0; i< locales.length;i++) {
					var lugar = locales[i];
					var markerPos = new google.maps.LatLng(lugar.latitud, lugar.longitud);
            		var marker = new google.maps.Marker({
                		position: markerPos,
	                	map: _map,
		                icon:image
					});
					kuesty.setMapInfoWindow(marker, lugar);
					_markers.push(marker);

				};
				$("#nearbyList").html(data.html);
			});
		
	}

	this.nearby = function() {

		var page = localStorage.getItem("nearbyPagination");
		if(page == null) {
			page = 1;
		}else {
			page = parseInt(page);
			// page = page < 2 ? page + 1 : page;
			page++;
		}
		if(page > 2) {
			return;
		}
		localStorage.setItem("nearbyPagination", page);
		$.get("/ajax/nearby/?page="+ page, function(data){
			$("#geoPermiso").hide();
			$("#nearbyList").append(data.html);
			if(page == 2) {
				$("#nearbyList").parent().find(".view-more").hide();		
			}
		}, 'json');
	}

	this.getNearby = function(lat,lng, callback) {
	
		var url = "/ajax/getnearby/?";
			url+= "lat="+lat+"&long="+lng;
	
		$.get(url, function(data){
			callback(data);
		}, 'json');
	}

	this.addhorariolocal = function() {
	
		var dia = $("#horario_dia").val();
		var hora_ini = $("#horario_hora_ini").val();
		var hora_fin = $("#horario_hora_fin").val();
		var dia_nom  = ["lunes","martes","miercoles","jueves","viernes","sabado","domingo"][dia];

		// md5 javascript
		var fid = hex_md5(dia+"-"+hora_ini+"-"+hora_fin);

		var html = '<div id="horario_'+fid+'" class="unhorario">';
			html += '<input type="hidden" name="horarios['+fid+'][dia]" value="'+dia+'" />';
			html += '<input type="hidden" name="horarios['+fid+'][hora_ini]" value="'+hora_ini+'" />';
			html += '<input type="hidden" name="horarios['+fid+'][hora_fin]" value="'+hora_fin+'" />';
			html += '<p>'+dia_nom+' de '+hora_ini+' a '+hora_fin;
			html += ' <a onclick="$(\'#horario_'+fid+'\').remove()">eliminar</a>';
			html += '</p>';
			html += '</div>';

		$("#horarios_list").append(html);

	}

	this.sendMessage = function() {
	
		var msg = $("#reply_textarea").val();
		if(jQuery.trim(msg) == "") return;

		var reuser = $("#destinatario").val();
	//	var reid = $("#reply_reid").val();
		
		$.post('/messages/send/?reuser='+reuser+"&mensaje="+encodeURIComponent(msg),{}, function(data){
			$("#reply_textarea").val("");
			console.log(data);
			if(data.error) {
				if(data.msg) {
					alert(data.msg);
				}
				return;
			}
			document.location.href = "/messages/conversation/?cid="+data.convid;
			/*
			$(".cnv_scroll").append(data.nmsg);
			sc+=90;
			$(".cnv_scroll").animate({ scrollTop: sc }, 1000);
			*/
		},'json');
	}

	this.generateCheckinPreview = function() {

		var dia = $("#dia").val();
			dia = parseInt(dia);

		switch(dia) { 
			case 1 : var dia_nombre = "lunes"; break;
			case 2 : var dia_nombre = "martes"; break;
			case 3 : var dia_nombre = "miercoles"; break;
			case 4 : var dia_nombre = "jueves"; break;
			case 5 : var dia_nombre = "viernes"; break;
			case 6 : var dia_nombre = "sabado"; break;
			case 7 : var dia_nombre = "domingo"; break;
			default : var dia_nombre = "dias"; break;
		}
		$("#preview_dia").text(dia_nombre);

		if ($('#sin_hora').is(':checked')) {
			$("#preview_hora").text("");	
		}else {
			var hora_ini = $("#hora_ini").val().toString();
			var hora_fin = $("#hora_fin").val().toString();
			var html = "de ";
				html+= hora_ini;
				html+=" a ";
				html+= hora_fin;
			
			$("#preview_hora").text(html);
		
		}
		$("#preview_desc").text($("#descripcion").val());
		$("#preview_fecha_ini").text($("#fecha_ini").val());
		$("#preview_fecha_fin").text($("#fecha_fin").val());
	
	}

	this.disableHorario= function(d) {
	
		if ($('#hora_cerrado_'+d).is(':checked')) {
			$("#hora_ini_"+d).attr("disabled","disabled");
			$("#hora_fin_"+d).attr("disabled","disabled");
		} else {
			$("#hora_ini_"+d).removeAttr("disabled");
			$("#hora_fin_"+d).removeAttr("disabled");
		} 
	}

	this.replyMessage = function() {
	
		var msg = $("#reply_textarea").val();
		if(jQuery.trim(msg) == "") return;

		var reid = $("#reply_reid").val();
		
		$.post('/messages/reply/?reid='+reid+"&mensaje="+encodeURIComponent(msg),{}, function(data){
			$("#reply_textarea").val("");
			console.log(data);
			if(data.error) {
				if(data.msg) {
					alert(data.msg);
				}
				return;
			}
			$(".cnv_scroll").append(data.nmsg);
			sc+=90;
			$(".cnv_scroll").animate({ scrollTop: sc }, 1000);

		},'json');
	}

	this.filterComunidad= function(select, fb) {
		if(fb == undefined) {
			document.location.href = "/comunidad/" + select.value;
		}else {
			document.location.href = "http://kuesty.com/facebookapp/comunidad/?filter=" + select.value;
		}
	}

	this.drawMap = function(locales, container_id) {

		if(localStorage.getItem("geoPositionLat") == undefined) {
			navigator.geolocation.getCurrentPosition(function(geo) {
				_geo = geo;
				localStorage.setItem("geoPositionLat",geo.coords.latitude);
				localStorage.setItem("geoPositionLon",geo.coords.longitude);
				lat = geo.coords.latitude
				lng = geo.coords.longitude
			});			
		}else {
			lat = localStorage.getItem("geoPositionLat")
			lng = localStorage.getItem("geoPositionLon")
		}

	
		var myLatlng = new google.maps.LatLng(lat, lng);
        var myOptions = {
               	zoom: 13,
               	center: myLatlng,
        		panControl: true,
				overviewMapControl:false,
				rotateControl:false,
				mapTypeControl:false,
               	mapTypeId: google.maps.MapTypeId.ROADMAP
         };

        _map = new google.maps.Map(
            document.getElementById(container_id),
            myOptions
        );

        //marco mi posicion
        var myMarker = new google.maps.Marker({
               position: myLatlng,
               map: _map,
            id : 0,
               icon: '/resources/images/map_pin_me.png',
            flat: true
        });

        var image = '/resources/images/map_pin_off.png';
        // creo los puntos
        for(key in locales) {
            var lugar = locales[key]

            var markerPos = new google.maps.LatLng(lugar.lat, lugar.lng);
            var marker = new google.maps.Marker({
                position: markerPos,
                map: _map,
                icon:image
            });
			
			this.setMapInfoWindow(marker, lugar);            
		}

	}

	this.setMapInfoWindow = function(marker, lugar) {
	
			var content = '<div class="cloud"'
				content+= 'onclick="document.location.href=\'/restaurants/local/id/' + lugar.id + '\';"';
				content+= '>';
				content+= '<img width="70px" height="auto" src="'+lugar.logo_mobile+'" style="float:right;margin-right:6px;" />';
				content+= '<p><b>' + lugar.nombre + '</b></p>';
				content+= '<p>' + lugar.direccion + '</p>';
			
				if(lugar.logo_mobile == null) {
					lugar.logo_mobile = "http://kuesty.com/resources/images/no_image.png" 
				}
				content+= '<p> ' + lugar.cantidad_calificaciones + ' calificaciones</p>';
				content+= '<div style="">';
				content+= '<span class="stars">';
				var f = Math.floor(lugar.stars);
				for(var i=0; i<5;i++) {
					if(f>i) {
						content+= '<a class="active"></a>';
					}else{
						content+= '<a></a>';
					}
				}
				content+= '</span>';

				content+= '<span class="dolar">';
				var f = Math.floor(lugar.pesos);
				for(var i=0; i<5;i++) {
					if(f>i) {
						content+= '<a class="active"></a>';
					}else{
						content+= '<a></a>';
					}
				}
				content+= '</span>';
				content+= '</div>';
				content+= '</div>';

        	var infowindow = new google.maps.InfoWindow({ 
				content: content
			});

			google.maps.event.addListener(marker, 'click', function() {
				_map.setCenter(marker.position);
				marker.setIcon("/resources/images/map_pin.png");

				try { 
					$(".user-profile2").css("background-color", "#eee");
					$("#"+lugar.ancla).css("background-color", "#cbcbcb");
				}catch(e){
					alert(e);
				};

				try { lastInfoWindow.close(); }catch(e) { }
				try { lastMarker.setIcon("/resources/images/map_pin_off.png"); }catch(e) { }
				
				lastInfoWindow = infowindow;
				lastMarker = marker;
				
				//document.location.href= "#"+lugar.ancla;
				try { $.scrollTo("#"+lugar.ancla, 800); }catch(e) {  }
				infowindow.open(_map,marker);
			});

	}

	this.moreTop = function() {
		var page = localStorage.getItem("topPagination");
		if(page == null) {
			page = 1;
		}else {
			page = parseInt(page);
			// page = page < 2 ? page + 1 : page;
			page++;
		}
		if(page > 2) {
			return;
		}
		localStorage.setItem("topPagination", page);
		$.get("/ajax/moretop/?page="+ page, function(data){
			$("#topList").append(data.html);
			if(page == 2) {
				$("#topList").parent().find(".view-more").hide();		
			}
		}, 'json');
	}

	this.loadCheckinsUser = function() {
		var page = localStorage.getItem("userCheckins");
		if(page == "0") {
			page = 1;
		}else {
			page = parseInt(page);
			// page = page < 2 ? page + 1 : page;
			page++;
		}
		console.log(page)
		localStorage.setItem("userCheckins", page);
		iduser = $("#id_user").html();
		$.get("/ajax/morecheckins/?id="+iduser+"&page="+ page, function(data){
			console.log(data)
			$("#userCheckinContainer > button").before(data.html);
			if(page*4 > data.total) {
				$("#userCheckinContainer").parent().find(".view-more").hide();		
			}
		}, 'json');
	}

	this.claimTarjeta = function(idlocal) {
			
		if(idlocal == undefined) {
			alert("Wops! Ha ocurrido un error! Por favor intenta nuevamente o comunicate a info@kuesty.com!");
			return;
		}
     	$.post('/ajax/claim',{"id_local":idlocal},function(data){
			if(data.error) {
				alert(data.message);
				return;
			}
			alert("Ya recibimos tu reclamo, nos comunicaremos a la brevedad!");
			$("#claimTarjeta").hide();
			/*
			$("#reclamarLocalBtt > button").attr("onclick", "");
			$("#reclamarLocalBtt > button").unbind("click");
			$("#reclamarLocalBtt > button").click(function(){});
			*/
			
		},'json');

	}

	this.claim = function(idlocal) {
	
		$(function() {
        	$( "#dialog-modal" ).dialog({
            	height: 600,
				width: 780,
	            modal: true
    	    });
    	});
		/*
			*/
 	}
	
	this.enableStatusEdit = function(id_local, button) {
		console.log(id_local);
		console.log(button);
		$(".status > p").hide();
		$(".status > textarea").show();
		$(".status > textarea").removeAttr("disabled");	
		$(".status > textarea").css("background-color", "#fff");
		$(button).text("Guardar nuevo estado");
		$(button).attr("onclick", "kuesty.addLocalStatus(" + id_local + ");");
	}

	this.addLocalStatus = function(id_local) {
		if(id_local == undefined) {
			return;
		}
		var post = $("#localStatus").val();

		$.post('/ajax/addlocalstatus',{"id_local":id_local, "post":post},function(data){
			if(data.error) {
				alert(data.msg);
				return;
			}
			alert(data.msg);
			//document.location.href = document.location.href;
			$("#reclamarLocalBtutton").hide();
			$(".status > p").text(post);
			$(".status > p").show();
			$(".status > textarea").hide();

		},'json');


	}

	this.addFan = function(idlocal) {
	
		if(idlocal == undefined) { 
			return;
		}
		$.post('/ajax/addfan',{"id_local":idlocal},function(data){
			if(data.error) {
				alert("Ha ocurrido un error, por favor intenta nuevamente mas tarde!");
				return;
			}
			if(data.aux > data.cant_fans) {
				alert("Ya no eres fan de este local");
				$("addfanbtt").text("Hazte fan");
			}else {
				alert("Ahora eres fan de este local");
				$("addfanbtt").text("Dejar de ser fan");
				/*
				FB.api(
  					'/me/kuestyapp:fan',
					'post', {
    					restaurant: "http://kuesty.com/restaurants/local/id/"+idlocal
  					},
  					function(response) {
						if(response.error.type == "OAuthException") {
						var url = "https://www.facebook.com/dialog/oauth?client_id= 106941089437708&redirect_uri=http://kuesty.com/restaurants/local/id/582";
						window.open();
						
    					// handle the response
	  				}
				);*/

			}
			$(".friends").html("<span></span>" + data.cant_fans +" fans");
		},'json');

	}

	this.getGeolocation = function() {

		$("#geoButton").unbind("click");
		$("#geoButton").text("Obteniendo localizacion");

		navigator.geolocation.getCurrentPosition(function(geo){ 
			coords = geo.coords;
			localStorage.setItem("coords", coords);
			localStorage.setItem("lat", coords.latitude);
			localStorage.setItem("lng", coords.longitude);
			$.get("/ajax/visitorcoords?lat="+coords.latitude+"&lng="+coords.longitude, function(locales){
				$("#geoButton").text("Buscando restaurantes!");
				kuesty.nearby();
			}, 'json');
		},function(){
			$("#geoButton").text("No has aceptado, :(");
			$("#geoButton").click(function(){ document.location.href = "/"; });
		});
	}

	this.categorias = [];
	this.getProvincias = function(pais,callback,idselect){
		$.post('/ajax/getprovincias',{pais:pais},function(data){
			callback(data,idselect)
		},'json')
	}

	this.orderByRated = function( order ){

		$( '#orderRated' ).val( order );
		this.searchRestaurant();

	}

	this.searchRestaurant = function( page ){

		if( page == undefined ){
			page = 0;
		}else{
			--page;
		}

		order = $( '#orderRated' ).val();

		if( order == '' )
			order = 'desc';

		patron = $( '#searchByString' ).val();
		if( patron == 'Search a restaurant' )
			patron = '';
		pais = $( '#selectPaises' ).val();
		provincia = $( '#selectProvincias' ).val();
		localidad = $( '#selectLocalidades' ).val();
		categoria = $( '#listCategorias' ).find( 'li' );
		categorias = '';

		if( categoria.length ){

		    categoria.each( function(e){

			categorias += $( this ).attr( 'id' ) + ',' ;

		       } )

		} 

		$.post('/ajax/searchrestaurant',{patron:patron,pais:pais,provincia:provincia,localidad:localidad,categorias:categorias,page:page,order:order},function(data){

			div = '';
			len = data.locales.length;

			if( len > 0 ){

				for( var i=0; i<len; i++ )	{

					var local = data.locales[i];
					destacado = ( ( i % 2 ) == 0 ) ? 'destacado' : '';

					div += '<div class="post_restaurants ' + destacado + '"  >';
					div += '<div class="thumb"><img border="0" src="img_example/thumb_toprated.png"></div><div class="name">';
					div += '<a href="restaurants/restaurant/id/' + local.id + '" >';
					div += local.nombre + '</a><div class="etiquetas">' + local.descripcion +'</div></div>';
					div += '<div class="reviewed">' + local.cantidad_reviews + ' reviewed</div>';
					div += '<div class="rate">';
					for( var stars=1; stars<=local.stars; stars++ )
						div += '<a class="activa"></a>';

					if( stars <= 5 )
						for( var starsInactive=stars; starsInactive<=5; starsInactive++ )
							div += '<a></a>';
					
					div += '</div>';
					div += '<div class="details"><p>' + local.direccion + '</p><p> '+ local.telefono + ' </p></div><br clear="all"></div>';

				}

				$( '.paginador' ).html( data.paginador );

			} else {

//				Si no se encontraron resultados
				div += 'No entra ni a palos';

			}
			
			$( '.restaurants' ).html( div );

		},'json')

	}

	this.llenarSelect = function(datos,id){
		var xhtml ="<option value=''>Seleccione</option>"
		var cant = datos.length;
		for(var i=0;i<cant;i++){
			var dato = datos[i]
			xhtml += "<option value="+dato.id+"> "+dato.nombre+"</option> "
		}
		$('#'+id).html(xhtml);
	}
	this.getLocalidades = function(prov,callback,idselect){
		$.post('/ajax/getlocalidades',{prov:prov},function(data){
			callback(data,idselect)
		},'json')
	}

	this.agregarCategoria = function(){

		categoria = $( '#selectCategorias option:selected' );
		id = categoria.val();

		if( $('#cat_' +id ).length )
			return;
		categoria = categoria.text();

		str = "<li id='" + id + "'><p>" + categoria  + "&nbsp;<div onclick='kuesty.quitarCategoria( " + id + " )'>Eliminar</div> </p><br clear='all' /></li>";
		$( '#listCategorias' ).append( str );

	}
	this.quitarCategoria = function( id ){

		$( '#' + id ).remove();
	}

	this.editField = function(id,tipo_elem,val,campo,userid){
		var xhtml = ""
		if(tipo_elem == "textarea"){
			xhtml += "<textarea onblur='kuesty.saveUserField(\""+id+"\",this,\""+campo+"\","+userid+",\""+tipo_elem+"\")'>"+val+"</textarea>"
		}else{
			xhtml += "<input onblur='kuesty.saveUserField(\""+id+"\",this,\""+campo+"\","+userid+",\""+tipo_elem+"\")' value='"+val+"' />"
		}
		
		$("#"+id).html(xhtml)
	
	}
	this.saveUserField = function(id_cont,elem,campo,userid,tipo_elem){
		valor = elem.value;
		$.post('/ajax/saveuserfield',{campo:campo,userid:userid,valor:valor},function(data){
			var xhtml = "<p onclick=\"kuesty.editField('"+id_cont+"','"+tipo_elem+"','"+valor+"','"+campo+"',"+userid+")\">"+valor+"</p>";
			$("#"+id_cont).html(xhtml);
		},'json')
	}

	this.SaveUserConfig = function(userid){
		var valor = document.getElementById("show_activity").value == 'on' ? 1 : 0;
		$.post('/ajax/saveuserfield',{campo:"public",userid:userid,valor:valor},function(data){
			window.location.href = "/user"
			return;
		},'json')
	}
	this.validarRegistro = function(){

		var campos = [];
		var radio = false;
		$("#signup_form :input").each(function(index) {
			if($(this).attr("name") == "sexo"){
				if(!radio){
					var campo = {"name":"sexo","val":$(":checked").val()}
					radio = true;
					campos.push(campo)
				}
			}else{
				var campo = {"name":$(this).attr('name'),"val":$(this).val()}
				campos.push(campo)
			}
		})
		console.log(campos)
		var params = "";
		var len = campos.length;
		for(var i = 0; i < len; i++) {
			var d = campos[i];
			params+= d.name + '=' + encodeURI(d.val) + '&';			 
		}
		$.post('/ajax/signup/?'+params,{},function(data){
			if(data.error){
				xhtml = ""
				//primero los pongo todos iguales
				$("#signup_form").find("input").css("background","url(/resources/images/bg_input.png) no-repeat center");
				for(var i in data.message){
					$("#signup_form").find("input[name="+i+"]").css("background","url(/resources/images/bg_input_error.png) no-repeat center");
					xhtml += "<li>* "+data.message[i]+"</li>"
					console.log(i);
				}
				$("#errores_form_registro").html(xhtml);
			}else{
				alert("Bienvenido, en su mail tendra una confirmacion");
				window.location.href = "/"
			}
			//console.log(data)
		},'json')
	}

	this.dejarDeSerFan = function( idRestaurant ){

		$.post('/ajax/dejardeserfan/?restaurant='+idRestaurant,{},function(data){

			if(data.error){

				if( data.error == 2 ){

					//Si el usuario no esta logueado
					document.location.href = '/signup/login';
					return;
				}			

			}

			$( '#fanBtn' ).text( 'Hazte FAN' );
			$( '#fanBtn' ).attr( 'onclick', 'kuesty.hazteFan(' + idRestaurant + ')' );

		},'json')

	}

	this.hazteFan = function( idRestaurant ){

		$.post('/ajax/haztefan/?restaurant='+idRestaurant,{},function(data){

			if(data.error){

				if( data.error == 2 ){

					//Si el usuario no esta logueado
					document.location.href = '/signup/login';
					return;

				}	

			}

			$( '#fanBtn' ).text( 'Dejar de ser FAN' );
			$( '#fanBtn' ).attr( 'onclick', 'kuesty.dejarDeSerFan(' + idRestaurant + ')' );

		},'json')

	}

	this.llenoEstrellas = function(cant,elem, pesos){
	
		elem.parent().find("a").removeClass("active");
		elem.prevAll().addClass("active");
		elem.addClass("active");
		if(pesos == undefined) {
			var aux  = $("#aux_estrellas").val(cant);
		}else {
			var aux  = $("#aux_pesos").val(cant);
		}
	}
		
	this.overEstrellas = function(cant, elem) {
	
		elem.parent().find("a").removeClass("active");
		elem.prevAll().addClass("active");
		elem.addClass("active");
	
	}

	this.outEstrellas = function(elem, pesos) {
		
		elem.parent().find("a").removeClass("active");
		if(pesos == undefined) {
			var aux  = $("#aux_estrellas").val();
		}else {
			var aux  = $("#aux_pesos").val();
		}
		if(aux == 0) {
			return;
		}
		var as = elem.parent().find("a");
		for(var i = 0; i < as.length; i++) {
			if(i < aux) {
				$(as[i]).addClass("active");
			}
		}
	}

	this.enviarResena = function(){
	
		var pesos,stars;
		//pesos = $("#calificacionPesos a.active").length;
		pesos = $("#aux_pesos").val();
		stars = $("#aux_estrellas").val();
		//stars = $("#calificacionEstrellas a.active").length;
		var review = jQuery.trim($("#contenidoReview").val());
		var local = $("#restoId").html();
		var shareFB = $("#shareFB").is(':checked');
	
		$.post("/ajax/addreview",{rating:stars,price:pesos,comentario:review,id_local:local,shareFB:shareFB},function(data){
			if(data.error != undefined) {
				alert(data.description);
				return;
			}
			$("#contenidoReview").val("");
			document.location.href = document.location.href;
			alert(data.msj);
		},'json');
	
	}

	this.addFriend = function(idFriend){

		$.post('/ajax/addfriend/?friend='+idFriend,{},function(data){
			if( data.error == 1 ){
				//Si el usuario no existe
				alert("el usuario que quieres agregar no existe");
				return;
			}
			if( data.error == 2 ){
				//Si el usuario no esta logueado
				document.location.href = '/signup/login';
				return;
			}
			document.location.href = document.location.href;
		},'json')
}

	this.acceptFriend = function(idfriend){
	
		$.post('/ajax/acceptfriend/?idfriend='+idfriend,{},function(data){
			if(data.error == 0){
				document.location.href = document.location.href;
			}	
			if(data.error == 2){
				alert("algo anda mal")
				return;
			}
			if(data.error == 1){
				alert("El usuario no existe")
				return;
			}
		},'json')
	}
	this.rejectFriend = function(idfriend){
	
		$.post('/ajax/rejectfriend/?idfriend='+idfriend,{},function(data){
			if(data.error){
				alert(data.error);
				return;
			}	
			document.location.href = document.location.href;
		},'json')
	}

	this.loadMoreComunidad = function(filtro){
	
		var page = window.localStorage.getItem("paginacomunidad");
		page = parseInt(page) + 1;
		$.post('/comunidad/'+filtro+'/?page='+page+'&ajax=1',{},function(data){
			$(".view-more").before(data.html);
			window.localStorage.setItem("paginacomunidad",page);
			if(data.len == 0) {
				$(".view-more").hide();
			}
		},'json')
	
	}

	this.verMasReviews = function( idLocal, idLastReview ){

		$.post('/ajax/getReviews/?lastreview='+idLastReview+'&local='+idLocal,{},function(data){

				if( data.error == 1 ){

					//Si se alcanzo la ultima review
					div = '<h2 style="text-align:center" class="titulo">' + data.description + '</h2>';
					$( '#verMasReviews' ).html( div );
					$( '#verMasReviews' ).removeAttr( 'onclick' );
					return;

				}

			len = data.reviews.length;
			
			for( i=0; i<len; i++ ){
				
				review = data.reviews[i];
				div = '<div class="modulo_reviews_resto"><div class="user_reviews"><div class="img"><img src="/resources/img_example/user_reviews.png"/></div>';
				div += '<p class="gris"><b>' + review.user + '</b></p>';
/*			<?php if( $this->user ) { ?>
				<?php if( !in_array( $review['id_user'], $this->friends ) ) { ?>
		                        <div class="add" id="addUser_<?=$review['id']?>" onclick="kuesty.addFriend(<?=$review['id_user']?>, this.id);" >+ add</div>
				<?php } ?>
			<?php } else { ?>
	                        <div class="add" id="addUser_<?=$review['id']?>" onclick="kuesty.addFriend(<?=$review['id_user']?>, this.id);" >+ add</div>
			<?php } ?>
                        <br clear="all" />
*/
				div += '</div>';
				div += '<div class="reviews_resto"><div class="rate rate_">';
/*			<?php 
				for( $star=1; $star<=5; $star++ ){ 
					$starsToShow = ( $star <= $review['rating'] ) ? '<a class="activa"></a>' : '<a></a>' ;
					echo $starsToShow;
				} 
			?>
                            <br clear="all" />
*/
                        	div += '</div>';
				div += '<div class="rate rate_ pesos_">';
/*
			<?php 
				for( $pesos=1; $pesos<=5; $pesos++ ){ 
					$pricesToShow = ( $pesos <= $review['price'] ) ? '<a class="activa"></a>' : '<a></a>' ;
					echo $pricesToShow;
				} 
			?>
                            <br clear="all" />
*/
                        div += '</div>';
			div += '<div class="fecha_restaurant"><?=date(d/m/Y, strtotime($review[fecha]) ) ?></div><br clear="all" />';
			div += '<p class="gris"><i>' + review.comentario + '</i></p></div><br clear="all" />';
                    	div += '<div class="gusto-nogusto"><a href="#" class="ok">' + review.likes + '</a><a href="#" class="nok">' + review.unlikes + '</a>';
			div += '<br clear="all"/></div></div>';

			}
			$( '#verMasReviews' ).prepend( div );


		},'json')

	}

	this.likeReview = function(id_review,like){
		
		$.post('/ajax/likereview/',{idreview:id_review,like:like},function(data){
			if(data.error == "1"){
				alert(decodeHTML(data.mensaje))
				return;
			}
			document.getElementById("likes_"+id_review).nextSibling.innerHTML =data.likes
			document.getElementById("unlikes_"+id_review).nextSibling.innerHTML =data.unlikes
		},'json')	

	}

	this.flagCheckin = function(id_checkin,elem){

		$.post('/ajax/flagcheckin/',{idcheckin:id_checkin},function(data){
			if(data.error == "1"){
				alert(decodeHTML(data.mensaje))
				return;
			}
			elem.innerHTML = "Gracias por avisarnos!";
			elem.onclick = function(){};
		},'json')	
		
	}

	this.updateComunidad = function() {
		$("#preloader").show();
		$.post('/comunidad/update',[],function(data){
			$("#preloader").hide();
			$("#comunidad_events").html(data.response);
		},'json');	
	}
	
	this.loadFriend = function(id) {
		document.location.href = "/user/id/"+id;
	}
	this.loadLocal = function(id) {
		document.location.href = "/restaurants/local/id/" + id;
	}
	this.loadReview = function(idlocal, idreview) {
		document.location.href = "/restaurants/restaurant/id/" + idlocal + '/review/' + idreview;
	}

}

var kuesty = new _kuesty();
function decodeHTML(encodedStr){
	var decoded = $("<div/>").html(encodedStr).text();
	return decoded;
}
$(function() {
		try{ $( "#user_tabs" ).tabs(); }catch(e){}
});

function alert(msg) {
	$("#dialog-message").find("p").text(msg);
	$("#dialog-message" ).dialog({
      modal: true,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}
/*
function confirm(msg) {
    $("#dialog-message").find("p").text(msg);
    $("#dialog-message").find("p").dialog({
      resizable: false,
      height:140,
      modal: true,
      buttons: {
        "Irme de aqui!": function() {
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}
*/
