<?
/**
 * User: Eduardo Romão
 * E-mail: eduardo.romao@outlook.com
 */

require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiNotificacaoDTO extends InfraDTO
{

  public function getStrNomeTabela()
    {
      return null;
  }

  public function montar()
    {
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_STR,
          'IdentificadorUsuario'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_STR,
          'Titulo'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_STR,
          'Mensagem'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_STR,
          'Resumo'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_STR,
          'UrlServicoNotificacao'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_STR,
          'ChaveAutorizacao'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_ARR,
          'Data'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_BOL,
          'Notificar'
      );
      $this->adicionarAtributo(
          InfraDTO::$PREFIXO_NUM,
          'IdApp'
      );
  }
}

?>

