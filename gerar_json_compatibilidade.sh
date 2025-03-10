JSON_FMT='{"name":"Módulo WSSEI", "version": "%s", "compatible_with": [%s]}'
VERSAO_MODULO=$(grep 'const VERSAO_MODULO' src/MdWsSeiRest.php | cut -d'"' -f2)
VERSOES=$(sed -n -e "/COMPATIBILIDADE_MODULO_SEI = array/,/;/ p" src/MdWsSeiRest.php \
           | sed -e '1d;$d' | sed -e '/\/\//d' \
           | sed -e "s/'/\"/g"| tr -d '\n'| tr -d ' ')

printf "$JSON_FMT" "$VERSAO_MODULO" "$VERSOES" > compatibilidade.json
