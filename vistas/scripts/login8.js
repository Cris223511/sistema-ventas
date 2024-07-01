$("#frmAcceso").on('submit', function (e) {
    e.preventDefault();
    login = $("#login").val();
    clave = $("#clave").val();

    console.log("hace la validación =)");

    $.post("../ajax/usuario.php?op=verificar", { "logina": login, "clavea": clave },
        function (data) {
            data = limpiarCadena(data);
            console.log(data);
            if (data == 0) {
                $("#btnGuardar").prop("disabled", false);
                Swal.fire({
                    icon: 'error',
                    title: 'Sin acceso',
                    text: 'Su usuario está desactivado, comuníquese con el administrador.',
                })
            } else if (data == 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sin acceso',
                    text: 'El usuario no se encuentra disponible, comuníquese con el administrador.',
                })
            } else if (data != "null") {
                Swal.fire({
                    icon: 'success',
                    title: 'Acceso correcto',
                    text: 'Te estamos redireccionando a la vista principal, espere un momento...'
                })
                setTimeout(function () {
                    $(location).attr("href", "escritorio.php");
                }, 1000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Sin acceso',
                    text: 'Usuario y/o contraseña incorrectos.',
                })
            }
        });
})

function mostrarOcultarClave() {
    console.log("di click =)");
    var claveInput = $('#clave');
    var ojitoIcon = $('#mostrarOcultarClave i');

    if ($('#rememberMe').is(':checked')) {
        claveInput.attr('type', 'text');
    } else {
        claveInput.attr('type', 'password');
    }
}

function mostrar() {
    $.post("../ajax/verPortada.php?op=mostrar", function (datas, status) {
        data = JSON.parse(datas);
        if (data != null) {
            console.log(data.imagen);
            $("#imagenmuestra").attr("src", "../files/portadas/" + data.imagen);
            $(".fondo-login").css("background-image", "url('../files/portadas/" + data.imagen + "')");
        } else {
            $("#imagenmuestra").attr("src", "../files/portadas/default.jpg");
            $(".fondo-login").css("background-image", "url('../files/portadas/default.jpg')");
        }
    });
}

function limpiarCadena(cadena) {
    let cadenaLimpia = cadena.trim();
    cadenaLimpia = cadenaLimpia.replace(/^[\n\r]+/, '');
    return cadenaLimpia;
}

mostrar();