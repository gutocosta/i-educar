<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                        *
*   @author Prefeitura Municipal de Itajaí                               *
*   @updated 29/03/2007                                                  *
*   Pacote: i-PLB Software Público Livre e Brasileiro                    *
*                                                                        *
*   Copyright (C) 2006  PMI - Prefeitura Municipal de Itajaí             *
*                       ctima@itajai.sc.gov.br                           *
*                                                                        *
*   Este  programa  é  software livre, você pode redistribuí-lo e/ou     *
*   modificá-lo sob os termos da Licença Pública Geral GNU, conforme     *
*   publicada pela Free  Software  Foundation,  tanto  a versão 2 da     *
*   Licença   como  (a  seu  critério)  qualquer  versão  mais  nova.    *
*                                                                        *
*   Este programa  é distribuído na expectativa de ser útil, mas SEM     *
*   QUALQUER GARANTIA. Sem mesmo a garantia implícita de COMERCIALI-     *
*   ZAÇÃO  ou  de ADEQUAÇÃO A QUALQUER PROPÓSITO EM PARTICULAR. Con-     *
*   sulte  a  Licença  Pública  Geral  GNU para obter mais detalhes.     *
*                                                                        *
*   Você  deve  ter  recebido uma cópia da Licença Pública Geral GNU     *
*   junto  com  este  programa. Se não, escreva para a Free Software     *
*   Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA     *
*   02111-1307, USA.                                                     *
*                                                                        *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/**
* @author Prefeitura Municipal de Itajaí
*
* Criado em 26/06/2006 16:19 pelo gerador automatico de classes
*/

require_once( "include/pmieducar/geral.inc.php" );

class clsPmieducarUsuario
{
    var $cod_usuario;
    var $ref_cod_escola;
    var $ref_cod_instituicao;
    var $ref_funcionario_cad;
    var $ref_funcionario_exc;
    var $ref_cod_tipo_usuario;
    var $data_cadastro;
    var $data_exclusao;
    var $ativo;

    // propriedades padrao

    /**
     * Armazena o total de resultados obtidos na ultima chamada ao metodo lista
     *
     * @var int
     */
    var $_total;

    /**
     * Nome do schema
     *
     * @var string
     */
    var $_schema;

    /**
     * Nome da tabela
     *
     * @var string
     */
    var $_tabela;

    /**
     * Lista separada por virgula, com os campos que devem ser selecionados na proxima chamado ao metodo lista
     *
     * @var string
     */
    var $_campos_lista;

    /**
     * Lista com todos os campos da tabela separados por virgula, padrao para selecao no metodo lista
     *
     * @var string
     */
    var $_todos_campos;

    /**
     * Valor que define a quantidade de registros a ser retornada pelo metodo lista
     *
     * @var int
     */
    var $_limite_quantidade;

    /**
     * Define o valor de offset no retorno dos registros no metodo lista
     *
     * @var int
     */
    var $_limite_offset;

    /**
     * Define o campo padrao para ser usado como padrao de ordenacao no metodo lista
     *
     * @var string
     */
    var $_campo_order_by;


    /**
     * Construtor (PHP 4)
     *
     * @return object
     */
    function __construct( $cod_usuario = null, $ref_cod_escola = null, $ref_cod_instituicao = null, $ref_funcionario_cad = null, $ref_funcionario_exc = null, $ref_cod_tipo_usuario = null, $data_cadastro = null, $data_exclusao = null, $ativo = null )
    {
        $db = new clsBanco();
        $this->_schema = "pmieducar.";
        $this->_tabela = "{$this->_schema}usuario";

        $this->_campos_lista = $this->_todos_campos = "u.cod_usuario, u.ref_cod_instituicao, u.ref_funcionario_cad, u.ref_funcionario_exc, u.ref_cod_tipo_usuario, u.data_cadastro, u.data_exclusao, u.ativo";

        if( is_numeric( $ref_funcionario_exc ) )
        {
            if( class_exists( "clsFuncionario" ) )
            {
                $tmp_obj = new clsFuncionario( $ref_funcionario_exc );
                if( method_exists( $tmp_obj, "existe") )
                {
                    if( $tmp_obj->existe() )
                    {
                        $this->ref_funcionario_exc = $ref_funcionario_exc;
                    }
                }
                else if( method_exists( $tmp_obj, "detalhe") )
                {
                    if( $tmp_obj->detalhe() )
                    {
                        $this->ref_funcionario_exc = $ref_funcionario_exc;
                    }
                }
            }
            else
            {
                if( $db->CampoUnico( "SELECT 1 FROM funcionario WHERE ref_cod_pessoa_fj = '{$ref_funcionario_exc}'" ) )
                {
                    $this->ref_funcionario_exc = $ref_funcionario_exc;
                }
            }
        }
        if( is_numeric( $ref_funcionario_cad ) )
        {
            if( class_exists( "clsFuncionario" ) )
            {
                $tmp_obj = new clsFuncionario( $ref_funcionario_cad );
                if( method_exists( $tmp_obj, "existe") )
                {
                    if( $tmp_obj->existe() )
                    {
                        $this->ref_funcionario_cad = $ref_funcionario_cad;
                    }
                }
                else if( method_exists( $tmp_obj, "detalhe") )
                {
                    if( $tmp_obj->detalhe() )
                    {
                        $this->ref_funcionario_cad = $ref_funcionario_cad;
                    }
                }
            }
            else
            {
                if( $db->CampoUnico( "SELECT 1 FROM funcionario WHERE ref_cod_pessoa_fj = '{$ref_funcionario_cad}'" ) )
                {
                    $this->ref_funcionario_cad = $ref_funcionario_cad;
                }
            }
        }
        if( is_numeric( $ref_cod_tipo_usuario ) )
        {
            if( class_exists( "clsPmieducarTipoUsuario" ) )
            {
                $tmp_obj = new clsPmieducarTipoUsuario( $ref_cod_tipo_usuario );
                if( method_exists( $tmp_obj, "existe") )
                {
                    if( $tmp_obj->existe() )
                    {
                        $this->ref_cod_tipo_usuario = $ref_cod_tipo_usuario;
                    }
                }
                else if( method_exists( $tmp_obj, "detalhe") )
                {
                    if( $tmp_obj->detalhe() )
                    {
                        $this->ref_cod_tipo_usuario = $ref_cod_tipo_usuario;
                    }
                }
            }
            else
            {
                if( $db->CampoUnico( "SELECT 1 FROM pmieducar.tipo_usuario WHERE cod_tipo_usuario = '{$ref_cod_tipo_usuario}'" ) )
                {
                    $this->ref_cod_tipo_usuario = $ref_cod_tipo_usuario;
                }
            }
        }
        if( is_numeric( $ref_cod_instituicao ) )
        {
            if( class_exists( "clsPmieducarInstituicao" ) )
            {
                $tmp_obj = new clsPmieducarInstituicao( $ref_cod_instituicao );
                if( method_exists( $tmp_obj, "existe") )
                {
                    if( $tmp_obj->existe() )
                    {
                        $this->ref_cod_instituicao = $ref_cod_instituicao;
                    }
                }
                else if( method_exists( $tmp_obj, "detalhe") )
                {
                    if( $tmp_obj->detalhe() )
                    {
                        $this->ref_cod_instituicao = $ref_cod_instituicao;
                    }
                }
            }
            else
            {
                if( $db->CampoUnico( "SELECT 1 FROM pmieducar.instituicao WHERE cod_instituicao = '{$ref_cod_instituicao}'" ) )
                {
                    $this->ref_cod_instituicao = $ref_cod_instituicao;
                }
            }
        }
        if( is_numeric( $ref_cod_escola ) )
        {
            if( class_exists( "clsPmieducarEscola" ) )
            {
                $tmp_obj = new clsPmieducarEscola( $ref_cod_escola );
                if( method_exists( $tmp_obj, "existe") )
                {
                    if( $tmp_obj->existe() )
                    {
                        $this->ref_cod_escola = $ref_cod_escola;
                    }
                }
                else if( method_exists( $tmp_obj, "detalhe") )
                {
                    if( $tmp_obj->detalhe() )
                    {
                        $this->ref_cod_escola = $ref_cod_escola;
                    }
                }
            }
            else
            {
                if( $db->CampoUnico( "SELECT 1 FROM pmieducar.escola WHERE cod_escola = '{$ref_cod_escola}'" ) )
                {
                    $this->ref_cod_escola = $ref_cod_escola;
                }
            }
        }
        if( is_numeric( $cod_usuario ) )
        {
            if( class_exists( "clsFuncionario" ) )
            {
                $tmp_obj = new clsFuncionario( $cod_usuario );
                if( method_exists( $tmp_obj, "existe") )
                {
                    if( $tmp_obj->existe() )
                    {
                        $this->cod_usuario = $cod_usuario;
                    }
                }
                else if( method_exists( $tmp_obj, "detalhe") )
                {
                    if( $tmp_obj->detalhe() )
                    {
                        $this->cod_usuario = $cod_usuario;
                    }
                }
            }
            else
            {
                if( $db->CampoUnico( "SELECT 1 FROM funcionario WHERE ref_cod_pessoa_fj = '{$cod_usuario}'" ) )
                {
                    $this->cod_usuario = $cod_usuario;
                }
            }
        }


        if( is_string( $data_cadastro ) )
        {
            $this->data_cadastro = $data_cadastro;
        }
        if( is_string( $data_exclusao ) )
        {
            $this->data_exclusao = $data_exclusao;
        }
        if( is_numeric( $ativo ) )
        {
            $this->ativo = $ativo;
        }
    }

    /**
     * Cria um novo registro
     *
     * @return bool
     */
    function cadastra()
    {
        if( is_numeric( $this->cod_usuario ) && is_numeric( $this->ref_funcionario_cad ) && is_numeric( $this->ref_cod_tipo_usuario ) )
        {
            $db = new clsBanco();

            $campos = "";
            $valores = "";
            $gruda = "";

            if( is_numeric( $this->cod_usuario ) )
            {
                $campos .= "{$gruda}cod_usuario";
                $valores .= "{$gruda}'{$this->cod_usuario}'";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_cod_escola ) )
            {
                $campos .= "{$gruda}ref_cod_escola";
                $valores .= "{$gruda}'{$this->ref_cod_escola}'";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_cod_instituicao ) )
            {
                $campos .= "{$gruda}ref_cod_instituicao";
                $valores .= "{$gruda}'{$this->ref_cod_instituicao}'";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_funcionario_cad ) )
            {
                $campos .= "{$gruda}ref_funcionario_cad";
                $valores .= "{$gruda}'{$this->ref_funcionario_cad}'";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_cod_tipo_usuario ) )
            {
                $campos .= "{$gruda}ref_cod_tipo_usuario";
                $valores .= "{$gruda}'{$this->ref_cod_tipo_usuario}'";
                $gruda = ", ";
            }
            $campos .= "{$gruda}data_cadastro";
            $valores .= "{$gruda}NOW()";
            $gruda = ", ";
            $campos .= "{$gruda}ativo";
            $valores .= "{$gruda}'1'";
            $gruda = ", ";

            $db->Consulta( "INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )" );
            //return $db->InsertId( "{$this->_tabela}_cod_usuario_seq");
            return $db->CampoUnico("SELECT 1 FROM {$this->_tabela} WHERE cod_usuario={$this->cod_usuario}");
        }
        return false;
    }

    /**
     * Edita os dados de um registro
     *
     * @return bool
     */
    function edita()
    {
        if( is_numeric( $this->cod_usuario ) && is_numeric( $this->ref_funcionario_exc ) )
        {

            $db = new clsBanco();
            $set = "";

            if( is_numeric( $this->ref_cod_instituicao ) )
            {
                $set .= "{$gruda}ref_cod_instituicao = '{$this->ref_cod_instituicao}'";
                $gruda = ", ";
            }
            else
            {
                $set .= "{$gruda}ref_cod_instituicao = null";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_funcionario_cad ) )
            {
                $set .= "{$gruda}ref_funcionario_cad = '{$this->ref_funcionario_cad}'";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_funcionario_exc ) )
            {
                $set .= "{$gruda}ref_funcionario_exc = '{$this->ref_funcionario_exc}'";
                $gruda = ", ";
            }
            if( is_numeric( $this->ref_cod_tipo_usuario ) )
            {
                $set .= "{$gruda}ref_cod_tipo_usuario = '{$this->ref_cod_tipo_usuario}'";
                $gruda = ", ";
            }
            if( is_string( $this->data_cadastro ) )
            {
                $set .= "{$gruda}data_cadastro = '{$this->data_cadastro}'";
                $gruda = ", ";
            }
            $set .= "{$gruda}data_exclusao = NOW()";
            $gruda = ", ";
            if( is_numeric( $this->ativo ) )
            {
                $set .= "{$gruda}ativo = '{$this->ativo}'";
                $gruda = ", ";
            }

            if( $set )
            {
                $db->Consulta( "UPDATE {$this->_tabela} SET $set WHERE cod_usuario = '{$this->cod_usuario}'" );
                return true;
            }
        }
        return false;
    }

    /**
     * Retorna uma lista filtrados de acordo com os parametros
     *
     * @return array
     */
    function lista( $int_cod_usuario = null, $int_ref_cod_escola = null, $int_ref_cod_instituicao = null, $int_ref_funcionario_cad = null, $int_ref_funcionario_exc = null, $int_ref_cod_tipo_usuario = null, $date_data_cadastro_ini = null, $date_data_cadastro_fim = null, $date_data_exclusao_ini = null, $date_data_exclusao_fim = null, $int_ativo = null, $int_nivel = null )
    {
        $sql = "SELECT {$this->_campos_lista}, tu.nivel FROM {$this->_tabela} u, {$this->_schema}tipo_usuario tu";

        $whereAnd = " AND ";
        $filtros = " WHERE u.ref_cod_tipo_usuario = tu.cod_tipo_usuario ";

        if( is_numeric( $int_cod_usuario ) )
        {
            $filtros .= "{$whereAnd} u.cod_usuario = '{$int_cod_usuario}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_cod_escola ) )
        {
            $filtros .= "{$whereAnd} u.ref_cod_escola = '{$int_ref_cod_escola}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_cod_instituicao ) )
        {
            $filtros .= "{$whereAnd} u.ref_cod_instituicao = '{$int_ref_cod_instituicao}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_funcionario_cad ) )
        {
            $filtros .= "{$whereAnd} u.ref_funcionario_cad = '{$int_ref_funcionario_cad}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_funcionario_exc ) )
        {
            $filtros .= "{$whereAnd} u.ref_funcionario_exc = '{$int_ref_funcionario_exc}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_cod_tipo_usuario ) )
        {
            $filtros .= "{$whereAnd} u.ref_cod_tipo_usuario = '{$int_ref_cod_tipo_usuario}'";
            $whereAnd = " AND ";
        }
        if( is_string( $date_data_cadastro_ini ) )
        {
            $filtros .= "{$whereAnd} u.data_cadastro >= '{$date_data_cadastro_ini}'";
            $whereAnd = " AND ";
        }
        if( is_string( $date_data_cadastro_fim ) )
        {
            $filtros .= "{$whereAnd} u.data_cadastro <= '{$date_data_cadastro_fim}'";
            $whereAnd = " AND ";
        }
        if( is_string( $date_data_exclusao_ini ) )
        {
            $filtros .= "{$whereAnd} u.data_exclusao >= '{$date_data_exclusao_ini}'";
            $whereAnd = " AND ";
        }
        if( is_string( $date_data_exclusao_fim ) )
        {
            $filtros .= "{$whereAnd} u.data_exclusao <= '{$date_data_exclusao_fim}'";
            $whereAnd = " AND ";
        }
        if( is_null( $int_ativo ) || $int_ativo )
        {
            $filtros .= "{$whereAnd} u.ativo = '1'";
            $whereAnd = " AND ";
        }
        else
        {
            $filtros .= "{$whereAnd} u.ativo = '0'";
            $whereAnd = " AND ";
        }
        if( is_numeric($int_nivel))
        {
            $filtros .= "{$whereAnd} tu.nivel = '$int_nivel'";
            $whereAnd = " AND ";
        }

        $db = new clsBanco();
        $countCampos = count( explode( ",", $this->_campos_lista ) );
        $resultado = array();

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico( "SELECT COUNT(0) FROM {$this->_tabela} u, {$this->_schema}tipo_usuario tu {$filtros}" );

        $db->Consulta( $sql );

        if( $countCampos > 1 )
        {
            while ( $db->ProximoRegistro() )
            {
                $tupla = $db->Tupla();

                $tupla["_total"] = $this->_total;
                $resultado[] = $tupla;
            }
        }
        else
        {
            while ( $db->ProximoRegistro() )
            {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if( count( $resultado ) )
        {
            return $resultado;
        }
        return false;
    }

    /**
     * Retorna um array com os dados de um registro
     *
     * @return array
     */
    function detalhe()
    {
        if( is_numeric( $this->cod_usuario ) )
        {
            $db = new clsBanco();
            $db->Consulta( "SELECT {$this->_todos_campos} FROM {$this->_tabela} u WHERE u.cod_usuario = '{$this->cod_usuario}'" );
            $db->ProximoRegistro();
            return $db->Tupla();
        }
        return false;
    }

    function listaExportacao($int_ref_cod_escola = null,
                             $int_ref_cod_instituicao = null,
                             $int_ref_cod_tipo_usuario = null,
                             $int_ativo = null)
    {
        $sql = "SELECT p.nome,
                       f.matricula,
                       f.email,
                       CASE
                           WHEN f.ativo = 1 THEN 'Ativo'
                           ELSE 'Inativo'
                       END AS status,
                       tu.nm_tipo,
                       i.nm_instituicao,
                       (select REPLACE(TEXTCAT_ALL(relatorio.get_nome_escola(ref_cod_escola)),'<br>',',') FROM pmieducar.escola_usuario 
                        WHERE ref_cod_usuario = u.cod_usuario".
                            (is_numeric( $int_ref_cod_escola ) ? " AND ref_cod_escola = '{$int_ref_cod_escola}'" :"").") AS nm_escola
                  FROM {$this->_tabela} u
                  INNER JOIN cadastro.pessoa p ON (p.idpes = u.cod_usuario)
                  INNER JOIN portal.funcionario f ON (f.ref_cod_pessoa_fj = p.idpes)
                  INNER JOIN pmieducar.tipo_usuario tu ON (tu.cod_tipo_usuario = u.ref_cod_tipo_usuario AND tu.ativo = 1)
                  INNER JOIN pmieducar.instituicao i ON (i.cod_instituicao = u.ref_cod_instituicao)";

        $whereAnd = " AND ";
        $filtros = " WHERE u.ref_cod_tipo_usuario = tu.cod_tipo_usuario ";

        if( is_numeric( $int_ref_cod_escola ) )
        {
            $filtros .= "{$whereAnd} exists(select 1 FROM pmieducar.escola_usuario WHERE ref_cod_usuario = u.cod_usuario AND ref_cod_escola = '{$int_ref_cod_escola}')";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_cod_instituicao ) )
        {
            $filtros .= "{$whereAnd} u.ref_cod_instituicao = '{$int_ref_cod_instituicao}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ref_cod_tipo_usuario ) )
        {
            $filtros .= "{$whereAnd} u.ref_cod_tipo_usuario = '{$int_ref_cod_tipo_usuario}'";
            $whereAnd = " AND ";
        }
        if( is_numeric( $int_ativo ) || $int_ativo )
        {
            $filtros .= "{$whereAnd} f.ativo = '{$int_ativo}'";
            $whereAnd = " AND ";
        }

        $db = new clsBanco();
        $countCampos = count( explode( ",", $this->_campos_lista ) );
        $resultado = array();

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();
        $db->Consulta( $sql );

        if( $countCampos > 1 )
        {
            while ( $db->ProximoRegistro() )
            {
                $tupla = $db->Tupla();

                $tupla["_total"] = $this->_total;
                $resultado[] = $tupla;
            }
        }
        else
        {
            while ( $db->ProximoRegistro() )
            {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if( count( $resultado ) )
        {
            return $resultado;
        }
        return false;
    }

    /**
     * Retorna um array com os dados de um registro
     *
     * @return array
     */
    function existe()
    {
        if( is_numeric( $this->cod_usuario ) )
        {
        $db = new clsBanco();
        $db->Consulta( "SELECT 1 FROM {$this->_tabela} WHERE cod_usuario = '{$this->cod_usuario}'" );
        $db->ProximoRegistro();
        return $db->Tupla();
        }
        return false;
    }

    /**
     * Exclui um registro
     *
     * @return bool
     */
    function excluir()
    {
        if( is_numeric( $this->cod_usuario ) && is_numeric( $this->ref_funcionario_exc ) )
        {

        /*
            delete
        $db = new clsBanco();
        $db->Consulta( "DELETE FROM {$this->_tabela} WHERE cod_usuario = '{$this->cod_usuario}'" );
        return true;
        */

        $this->ativo = 0;
            return $this->edita();
        }
        return false;
    }

    /**
     * Define quais campos da tabela serao selecionados na invocacao do metodo lista
     *
     * @return null
     */
    function setCamposLista( $str_campos )
    {
        $this->_campos_lista = $str_campos;
    }

    /**
     * Define que o metodo Lista devera retornoar todos os campos da tabela
     *
     * @return null
     */
    function resetCamposLista()
    {
        $this->_campos_lista = $this->_todos_campos;
    }

    /**
     * Define limites de retorno para o metodo lista
     *
     * @return null
     */
    function setLimite( $intLimiteQtd, $intLimiteOffset = null )
    {
        $this->_limite_quantidade = $intLimiteQtd;
        $this->_limite_offset = $intLimiteOffset;
    }

    /**
     * Retorna a string com o trecho da query resposavel pelo Limite de registros
     *
     * @return string
     */
    function getLimite()
    {
        if( is_numeric( $this->_limite_quantidade ) )
        {
            $retorno = " LIMIT {$this->_limite_quantidade}";
            if( is_numeric( $this->_limite_offset ) )
            {
                $retorno .= " OFFSET {$this->_limite_offset} ";
            }
            return $retorno;
        }
        return "";
    }

    /**
     * Define campo para ser utilizado como ordenacao no metolo lista
     *
     * @return null
     */
    function setOrderby( $strNomeCampo )
    {
        // limpa a string de possiveis erros (delete, insert, etc)
        //$strNomeCampo = eregi_replace();

        if( is_string( $strNomeCampo ) && $strNomeCampo )
        {
            $this->_campo_order_by = $strNomeCampo;
        }
    }

    /**
     * Retorna a string com o trecho da query resposavel pela Ordenacao dos registros
     *
     * @return string
     */
    function getOrderby()
    {
        if( is_string( $this->_campo_order_by ) )
        {
            return " ORDER BY {$this->_campo_order_by} ";
        }
        return "";
    }

}
?>