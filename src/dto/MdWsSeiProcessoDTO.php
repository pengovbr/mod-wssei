<?

/**
 * Class MdWsSeiProcessoDTO
 * DTO somente para encapsulamento de dados.
 * OBS: Nao estou usando API pois em modulos do SEI o autoload nao carrega as API's.
 */
class MdWsSeiProcessoDTO extends InfraDTO{

    public function getStrNomeTabela()
    {
        throw new InfraException('DTO nao utilizavel para consulta!');
    }

    public function montar(){
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Template');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdAtividade');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Valor');
    }

}
