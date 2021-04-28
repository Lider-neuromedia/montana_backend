<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="icon" href="../../assets/img/icons/foundation-favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width">
        <title>Restaurar Contraseña</title>
    </head>
    <body>
        <div style="width: 600px; height: 100%; margin-left: 96px;">
            <div class="imagen-superior">
                <img src="{{asset('email/imagen1.jpg')}}">
                <div class="imagen-inferiro" style="background-color: #FF596A; width: 100%;  height: 6px;   margin-bottom: 20px;margin: -6px 0px;"></div>
            </div>

            <h2 class="nombre-usuario"
                style="@@import url('https://fonts.googleapis.com/css2?family=Open+Sans&display=swap'); color: #E6323B; font-family: 'Open Sans', sans-serif;  font-size: 21px;  line-height: 25px; text-align: left;">
                ¡Hola, {{$nombre}}
            </h2>

            <hr style="width: 559px;">

            <div class="header-content" style="@@import url('https://fonts.googleapis.com/css2?family=Open+Sans&display=swap'); font-family: 'Open Sans', sans-serif; margin-left: 30px;">
                <p class="informacion-inicial" style="@@import url('https://fonts.googleapis.com/css2?family=Open+Sans&display=swap');color: #A3A3A3; font-family: 'Open Sans', sans-serif;  font-size: 13px;  line-height: 16px;  text-align: left;">
                    ¿Olvidaste tu contraseña?
                </p>
                <div style="padding-left: 30px;">
                    <p style="@@import url('https://fonts.googleapis.com/css2?family=Open+Sans&display=swap');  font-family: 'Open Sans', sans-serif;  font-size: 21px;  line-height: 25px; text-align: left;">
                        Te entregamos el siguiente token para que dentro de los siguientes {{$minutes}} minutos pueda cambiar su contraseña de usuario.
                    </p>

                    <p>Ingrese al siguiente enlace para restablecer su contraseña.</p>

                    <a style="color: #FF596A;" href="http://pruebasneuro.co/N-1065/#/restablecer-contraseña?token={{$token}}" target="_blank" rel="noopener noreferrer">
                        http://pruebasneuro.co/N-1065/#/restablecer-contraseña?token={{$token}}
                    </a>

                    <h3 style="@@import url('https://fonts.googleapis.com/css2?family=Open+Sans&display=swap'); font-family: 'Open Sans', sans-serif;  font-size: 21px;  line-height: 25px; text-align: left;">
                        Token: {{$token}}
                    </h3>

                    <div style="margin-top:100px;">
                        <p><a style="color: #FF596A;" href="{{env('FRONTEND_URL')}}" target="_blank">Ir a Athletic Air</a></p>
                    </div>
                </div>
            </div>
            <div class="footer-content">
                <div class="imagen-inferiro">
                    <img src="{{asset('email/imagen1.jpg')}}">
                </div>
            </div>
        </div>
    </body>
</html>