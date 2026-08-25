document.addEventListener('DOMContentLoaded', function () {


    /* =========================================================
       MOSTRAR / OCULTAR CONTRASEÑA
    ========================================================= */

    const botonesPassword =
        document.querySelectorAll('.toggle-password');


    botonesPassword.forEach(function (boton) {

        boton.addEventListener('click', function () {

            const targetId =
                boton.dataset.target;


            const input =
                document.getElementById(targetId);


            if (!input) {
                return;
            }


            const icono =
                boton.querySelector('i');


            if (
                input.type === 'password'
            ) {

                input.type = 'text';


                boton.setAttribute(
                    'aria-label',
                    'Ocultar contraseña'
                );


                boton.setAttribute(
                    'aria-pressed',
                    'true'
                );


                if (icono) {

                    icono.classList.remove(
                        'fa-eye'
                    );

                    icono.classList.add(
                        'fa-eye-slash'
                    );
                }


            } else {

                input.type = 'password';


                boton.setAttribute(
                    'aria-label',
                    'Mostrar contraseña'
                );


                boton.setAttribute(
                    'aria-pressed',
                    'false'
                );


                if (icono) {

                    icono.classList.remove(
                        'fa-eye-slash'
                    );

                    icono.classList.add(
                        'fa-eye'
                    );
                }
            }

        });

    });


    /* =========================================================
       ELEMENTOS DEL REGISTRO
    ========================================================= */

    const registerForm =
        document.getElementById(
            'registerForm'
        );


    const password =
        document.getElementById(
            'password'
        );


    const passwordConfirmacion =
        document.getElementById(
            'password_confirmacion'
        );


    const passwordMatch =
        document.getElementById(
            'passwordMatch'
        );


    const passwordRequirements =
        document.getElementById(
            'passwordRequirements'
        );


    /* =========================================================
       VALIDACIÓN VISUAL DE CONTRASEÑA
    ========================================================= */

    if (
        password &&
        passwordRequirements
    ) {

        password.addEventListener(
            'input',
            function () {

                const valor =
                    password.value;


                const longitud =
                    valor.length >= 8;


                const mayuscula =
                    /[A-Z]/.test(valor);


                const minuscula =
                    /[a-z]/.test(valor);


                const numero =
                    /[0-9]/.test(valor);


                if (
                    longitud &&
                    mayuscula &&
                    minuscula &&
                    numero
                ) {

                    passwordRequirements.textContent =
                        '✓ Contraseña segura.';

                } else {

                    passwordRequirements.textContent =
                        'Mínimo 8 caracteres, una mayúscula, una minúscula y un número.';
                }

            }
        );

    }


    /* =========================================================
       COMPROBAR CONTRASEÑAS
    ========================================================= */

    function comprobarContraseñas() {

        if (
            !password ||
            !passwordConfirmacion ||
            !passwordMatch
        ) {

            return true;
        }


        if (
            passwordConfirmacion.value === ''
        ) {

            passwordMatch.textContent = '';

            return true;
        }


        if (
            password.value ===
            passwordConfirmacion.value
        ) {

            passwordMatch.textContent =
                '✓ Las contraseñas coinciden.';


            return true;

        } else {

            passwordMatch.textContent =
                'Las contraseñas no coinciden.';


            return false;
        }

    }


    if (password) {

        password.addEventListener(
            'input',
            comprobarContraseñas
        );

    }


    if (passwordConfirmacion) {

        passwordConfirmacion.addEventListener(
            'input',
            comprobarContraseñas
        );

    }


    /* =========================================================
       VALIDAR REGISTRO ANTES DE ENVIAR
    ========================================================= */

    if (registerForm) {

        registerForm.addEventListener(
            'submit',
            function (event) {

                if (
                    password &&
                    passwordConfirmacion
                ) {

                    if (
                        password.value !==
                        passwordConfirmacion.value
                    ) {

                        event.preventDefault();


                        if (passwordMatch) {

                            passwordMatch.textContent =
                                'Las contraseñas no coinciden.';
                        }


                        passwordConfirmacion.focus();

                        return;
                    }


                    const contraseñaValida =
                        password.value.length >= 8 &&
                        /[A-Z]/.test(password.value) &&
                        /[a-z]/.test(password.value) &&
                        /[0-9]/.test(password.value);


                    if (!contraseñaValida) {

                        event.preventDefault();


                        if (passwordRequirements) {

                            passwordRequirements.textContent =
                                'La contraseña no cumple los requisitos.';
                        }


                        password.focus();

                        return;
                    }

                }


                /*
                 * Evitar doble envío.
                 */

                const boton =
                    document.getElementById(
                        'registerSubmit'
                    );


                if (boton) {

                    boton.disabled = true;

                    boton.textContent =
                        'Creando cuenta...';
                }

            }
        );

    }


    /* =========================================================
       EVITAR DOBLE ENVÍO DEL LOGIN
    ========================================================= */

    const loginForm =
        document.getElementById(
            'loginForm'
        );


    if (loginForm) {

        loginForm.addEventListener(
            'submit',
            function () {

                const boton =
                    document.getElementById(
                        'loginSubmit'
                    );


                if (boton) {

                    boton.disabled = true;

                    boton.textContent =
                        'Iniciando sesión...';
                }

            }
        );

    }

});