# Hacienda IdP

La idea principal de esta clase es proveer un mecanismo básico para el manejo de ACCESS_TOKEN y REFRESH_TOKEN del Identity Provider de Ministerio de Hacienda.

Sirve tanto para ambientes WEB como para ambientes CLI, ya que el almacenamiento de la sesión es un archivo JSON en disco.

Método de instalación/uso:

1) Ejecutar el comando "composer install"
2) Instanciar "$token = new Token($usuario, $contrasena);" con las credenciales del ATV

Cualquier detalle se puede consultar el archivo "test.php" para visualizar el uso básico