<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Prefeitura Municipal de Itajaí <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo disponível desde a versão 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'lib/Portabilis/Utils/Database.php';
require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/DataMapper/Utils.php';

/**
 * @author    Caroline Salib Canto <caroline@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     ?
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Importação educacenso');
    $this->processoAp = 9998849;
    $this->addEstilo('localizacaoSistema');
  }
}

class indice extends clsCadastro
{
  var $pessoa_logada;

  var $arquivo;

  function Inicializar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(9998849, $this->pessoa_logada, 7,
      'educar_index.php');
    $this->ref_cod_instituicao = $obj_permissoes->getInstituicao($this->pessoa_logada);

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "Início",
         "educar_educacenso_index.php" => "Educacenso",
         "" => "Importação educacenso"
    ));
    $this->enviaLocalizacao($localizacao->montar());

    return 'Editar';
  }

  function Gerar()
  {
    $this->campoArquivo('arquivo', 'Arquivo', $this->arquivo);
    $this->inputsHelper()->dynamic('ano', array('value' => $this->ano));
    $this->nome_url_sucesso = "Importar";
  }

  function Novo()
  {
    $this->Editar();
  }

  function Editar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(9998849, $this->pessoa_logada, 7,
      'educar_index.php');
    $this->ref_cod_instituicao = $obj_permissoes->getInstituicao($this->pessoa_logada);
    if(!$this->ref_cod_instituicao){
      $this->ref_cod_instituicao = 1;
    }

    if (!$this->arquivo['tmp_name']){
      $this->mensagem = "Selecione um arquivo para a exportação.";
      return false;
    }

    $arquivo = file_get_contents($this->arquivo['tmp_name']);

    $registros = explode("\n", $arquivo);
    usort($registros, 'comparar_registros');

    foreach ($registros as $registro) {
      $dadosRegistro = explode("|", $registro);
      $numeroRegistro = $dadosRegistro[0];

      switch ($numeroRegistro) {
        case '00':
          $this->importaRegistro00($dadosRegistro);
          break;
        case '10':
          $this->importaRegistro10($dadosRegistro);
          break;
        case '20':
          $this->importaRegistro20($dadosRegistro);
          break;
        case '30':
          $this->importaRegistro30($dadosRegistro);
          break;
      }
    }
    return true;
  }

  function comparar_registros($a, $b){
    return strnatcmp($a[0], $b[0]);
  }

  function importaRegistro00($dadosRegistro) {
    $this->mensagem = "Importação do registro 00";

    $inep = $dadosRegistro[1];
    $cpfGestor = (int) $dadosRegistro[2];
    $nomeGestor = utf8_encode($dadosRegistro[3]);
    $cargoGestor = $dadosRegistro[4];
    $emailGestor = $dadosRegistro[5];
    $situacao = $dadosRegistro[6];
    $dataInicioAnoLetivo = $dadosRegistro[7];
    $dataFimAnoLetivo = $dadosRegistro[8];
    $nomeEscola = utf8_encode($dadosRegistro[9]);
    $latitude = $dadosRegistro[10];
    $longitude = $dadosRegistro[11];
    $cep = $dadosRegistro[12]; // ep.cep
    $logradouro = $dadosRegistro[13]; // l.idtlog l.nome
    $enderecoNumero = $dadosRegistro[14]; // ep.numero
    $complemento = $dadosRegistro[15]; // ep.complemento
    $nomeBairro = $dadosRegistro[16]; // b.nome
    $ufIbge = $dadosRegistro[17]; // uf.cod_ibge
    $municipioIbge = $dadosRegistro[18]; // m.cod_ibge
    $distritoIbge = $dadosRegistro[19]; // d.cod_ibge
    $ddd = $dadosRegistro[20]; // fone_pessoa.ddd
    $telefone = $dadosRegistro[21]; // fone_pessoa.fone
    $telefonePublico = $dadosRegistro[22];
    $telefoneContato = $dadosRegistro[23]; // fone_pessoa.fone e tipo = 3
    $telefoneFAX = $dadosRegistro[24]; // fone_pessoa.fone e tipo = 4
    $email = $dadosRegistro[25];
    $codigoOrgaoRegional = ltrim($dadosRegistro[26], '0');
    $dependenciaAdministrativa = $dadosRegistro[27];
    $localizacao = $dadosRegistro[28]; // b.zona_localizacao
    $categoriaEscolaPrivada = $dadosRegistro[29];
    $convenioPoderPublico = $dadosRegistro[30];
    $mantenedorEmpresa = $dadosRegistro[31];
    $mantenedorSindicato = $dadosRegistro[32];
    $mantenedorOrganizacao = $dadosRegistro[33];
    $mantenedorInstituicao = $dadosRegistro[34];
    $mantenedorSistema = $dadosRegistro[35];
    $cnpjMantenedoraPrincipal = $dadosRegistro[36];
    $cnpj = $dadosRegistro[37];
    $regulamentacao = $dadosRegistro[38];
    $unidadeVinculada = $dadosRegistro[39];

    $idpesGestor = $this->existePessoa($cpfGestor);
    $pessoa        = new clsPessoa_();
    $pessoa->idpes = $idpesGestor;
    $pessoa->nome  = $nomeGestor;
    $pessoa->email = addslashes($emailGestor);
    if($idpesGestor){
      $pessoa->idpes_rev = $this->pessoa_logada;
      $pessoa->data_rev  = date('Y-m-d H:i:s', time());
      $pessoa->edita();

    }else{
      $pessoa->tipo      = 'F';
      $pessoa->idpes_cad = $this->pessoa_logada;
      $idpesGestor      = $pessoa->cadastra();

      $fisica = new clsFisica();
      $fisica->idpes                = $idpesGestor;
      $fisica->cpf = $cpfGestor;
      $fisica->cadastra();
    }

    $codEscola = $this->existeEscola($inep);

    if(!$codEscola){
      $pessoa = new clsPessoa_(
        null, $nomeEscola, null, null,
        'J', null, null, $email
      );
      $idpesEscola = $pessoa->cadastra();

      $cnpj = sprintf("%02d.%03d.%03d/%04d-%02d", rand(1, 99), rand(1, 999), rand(1, 999), rand(1, 9999), rand(1, 99));
      $juridica = new clsJuridica(
        $idpesEscola,idFederal2int($cnpj), $nomeEscola,
        null, null, $this->pessoa_logada, null
      );
      $juridica->cadastra();

      $codEscolaRedeEnsino = $this->getOrCreateRedeDeEnsino();

      $codEscolaLocalizacao = $this->getOrCreateLocalizacaoEscola($localizacao);

      $codEscola = $this->createEscola($codEscolaLocalizacao, $codEscolaRedeEnsino, $idpesEscola, $nomeEscola);

      if(!$codEscola){
        return false;
      }

      $this->createEscolaEducacenso($codEscola, $inep);
    }

    $this->atualizaCamposEscolaRegistro00($codEscola, $cargoGestor, $situacao, $latitude, $longitude, $codigoOrgaoRegional, $dependenciaAdministrativa, $regulamentacao);

    $this->cadastraEnderecoEscola($codEscola, $cep, $logradouro, $enderecoNumero, $complemento, $nomeBairro, $ufIbge, $municipioIbge, $distritoIbge, $localizacao);

    // TODO cadastrar telefones
  }

  function importaRegistro10($dadosRegistro){

    $inep = $dadosRegistro[2-1];

    $localFuncionamentoArray = array(
      '3' => $dadosRegistro[3-1],
      '4' => $dadosRegistro[4-1],
      '5' => $dadosRegistro[5-1],
      '6' => $dadosRegistro[6-1],
      '7' => $dadosRegistro[7-1],
      '8' => $dadosRegistro[8-1],
      '9' => $dadosRegistro[9-1],
      '10' => $dadosRegistro[10-1],
      '11' => $dadosRegistro[11-1],
    );
    $localFuncionamento = '0';
    foreach ($localFuncionamentoArray as $key => $value) {
      if($value == 1){
        $localFuncionamento = $key;
        break;
      }
    }

    $camposEscola = array(
      'local_funcionamento' => $localFuncionamento,
      'condicao' => $dadosRegistro[12-1],
      'codigo_inep_escola_compartilhada' => $dadosRegistro[14-1],
      'agua_consumida' => $dadosRegistro[20-1],
      'agua_rede_publica' => $dadosRegistro[21-1],
      'agua_poco_artesiano' => $dadosRegistro[22-1],
      'agua_cacimba_cisterna_poco' => $dadosRegistro[23-1],
      'agua_fonte_rio' => $dadosRegistro[24-1],
      'agua_inexistente' => $dadosRegistro[25-1],
      'energia_rede_publica' => $dadosRegistro[26-1],
      'energia_gerador' => $dadosRegistro[27-1],
      'energia_outros' => $dadosRegistro[28-1],
      'energia_inexistente' => $dadosRegistro[29-1],
      'esgoto_rede_publica' => $dadosRegistro[30-1],
      'esgoto_fossa' => $dadosRegistro[31-1],
      'esgoto_inexistente' => $dadosRegistro[32-1],
      'lixo_coleta_periodica' => $dadosRegistro[33-1],
      'lixo_queima' => $dadosRegistro[34-1],
      'lixo_joga_outra_area' => $dadosRegistro[35-1],
      'lixo_recicla' => $dadosRegistro[36-1],
      'lixo_enterra' => $dadosRegistro[37-1],
      'lixo_outros' => $dadosRegistro[38-1],
      'dependencia_sala_diretoria' => $dadosRegistro[39-1],
      'dependencia_sala_professores' => $dadosRegistro[40-1],
      'dependencia_sala_secretaria' => $dadosRegistro[41-1],
      'dependencia_laboratorio_informatica' => $dadosRegistro[42-1],
      'dependencia_laboratorio_ciencias' => $dadosRegistro[43-1],
      'dependencia_sala_aee' => $dadosRegistro[44-1],
      'dependencia_quadra_coberta' => $dadosRegistro[45-1],
      'dependencia_quadra_descoberta' => $dadosRegistro[46-1],
      'dependencia_cozinha' => $dadosRegistro[47-1],
      'dependencia_biblioteca' => $dadosRegistro[48-1],
      'dependencia_sala_leitura' => $dadosRegistro[49-1],
      'dependencia_parque_infantil' => $dadosRegistro[50-1],
      'dependencia_bercario' => $dadosRegistro[51-1],
      'dependencia_banheiro_fora' => $dadosRegistro[52-1],
      'dependencia_banheiro_dentro' => $dadosRegistro[53-1],
      'dependencia_banheiro_infantil' => $dadosRegistro[54-1],
      'dependencia_banheiro_deficiente' => $dadosRegistro[55-1],
      'dependencia_vias_deficiente' => $dadosRegistro[56-1],
      'dependencia_banheiro_chuveiro' => $dadosRegistro[57-1],
      'dependencia_refeitorio' => $dadosRegistro[58-1],
      'dependencia_dispensa' => $dadosRegistro[59-1],
      'dependencia_aumoxarifado' => $dadosRegistro[60-1],
      'dependencia_auditorio' => $dadosRegistro[61-1],
      'dependencia_patio_coberto' => $dadosRegistro[62-1],
      'dependencia_patio_descoberto' => $dadosRegistro[63-1],
      'dependencia_alojamento_aluno' => $dadosRegistro[64-1],
      'dependencia_alojamento_professor' => $dadosRegistro[65-1],
      'dependencia_area_verde' => $dadosRegistro[66-1],
      'dependencia_lavanderia' => $dadosRegistro[67-1],
      'dependencia_nenhuma_relacionada' => $dadosRegistro[68-1],
      'dependencia_numero_salas_existente' => $dadosRegistro[69-1],
      'dependencia_numero_salas_utilizadas' => $dadosRegistro[70-1],
      'televisoes' => $dadosRegistro[71-1],
      'videocassetes' => $dadosRegistro[72-1],
      'dvds' => $dadosRegistro[73-1],
      'antenas_parabolicas' => $dadosRegistro[74-1],
      'copiadoras' => $dadosRegistro[75-1],
      'retroprojetores' => $dadosRegistro[76-1],
      'impressoras' => $dadosRegistro[77-1],
      'aparelhos_de_som' => $dadosRegistro[78-1],
      'projetores_digitais' => $dadosRegistro[79-1],
      'faxs' => $dadosRegistro[80-1],
      'maquinas_fotograficas' => $dadosRegistro[81-1],
      'computadores' => $dadosRegistro[82-1],
      'impressoras_multifuncionais' => $dadosRegistro[83-1],
      'computadores_administrativo' => $dadosRegistro[84-1],
      'computadores_alunos' => $dadosRegistro[85-1],
      'acesso_internet' => $dadosRegistro[86-1],
      'banda_larga' => $dadosRegistro[87-1],
      'total_funcionario' => $dadosRegistro[88-1],
      'atendimento_aee' => $dadosRegistro[90-1],
      'atividade_complementar' => $dadosRegistro[91-1],
      'fundamental_ciclo' => $dadosRegistro[96-1],
      'localizacao_diferenciada' => $dadosRegistro[97-1],
      'didatico_nao_utiliza' => $dadosRegistro[98-1],
      'didatico_quilombola' => $dadosRegistro[99-1],
      'didatico_indigena' => $dadosRegistro[100-1],
      'educacao_indigena' => $dadosRegistro[101-1],
      'lingua_ministrada' => $dadosRegistro[104-1],
      'espaco_brasil_aprendizado' => $dadosRegistro[105-1],
      'abre_final_semana' => $dadosRegistro[106-1],
      'proposta_pedagogica' => $dadosRegistro[107-1],
      'codigo_lingua_indigena' => $dadosRegistro[104-1],
      'espaco_brasil_aprendizado' => $dadosRegistro[105-1],
      'abre_final_semana' => $dadosRegistro[106-1],
      'proposta_pedagogica' => $dadosRegistro[107-1],
    );

    $codEscola = $this->existeEscola($inep);
    if($codEscola){
      $objEscola = new clsPmieducarEscola($codEscola);
      $fields = $objEscola->detalhe();

      foreach ($fields as $key => $value) {
        if(property_exists($objEscola, $key)){
          $objEscola->{$key} = $value;
        }
      }
      foreach ($camposEscola as $key => $value) {
        $objEscola->{$key} = $value;
      }
      $objEscola->edita();
    }

  }

  function importaRegistro20($dadosRegistro){

    $inepEscola = $dadosRegistro[2-1];
    $inepTurma = $dadosRegistro[3-1];

    $nomeTurma = utf8_encode($dadosRegistro[5-1]);

    $horaInicial = sprintf("%02d:%02d:00", intval($dadosRegistro[7-1]), intval($dadosRegistro[8-1]));
    $horaFinal = sprintf("%02d:%02d:00", intval($dadosRegistro[9-1]), intval($dadosRegistro[10-1]));

    $diasSemana = array(
      'domingo' => $dadosRegistro[11-1],
      'segunda-feira' => $dadosRegistro[12-1],
      'terça-feira' => $dadosRegistro[13-1],
      'quarta-feira' => $dadosRegistro[14-1],
      'quinta-feira' => $dadosRegistro[15-1],
      'sexta-feira' => $dadosRegistro[16-1],
      'sabado' => $dadosRegistro[17-1],
    );

    $diasSemana = array();

    for ($i=1; $i <= 7; $i++) {
      if($dadosRegistro[10+$i-1] == 1){
        $diasSemana[] = $i;
      }
    }

    $camposTurma = array(
      'tipo_atendimento' => $dadosRegistro[18-1],
      'turma_mais_educacao' => $dadosRegistro[19-1],
      'atividade_complementar_1' => $dadosRegistro[20-1],
      'atividade_complementar_2' => $dadosRegistro[21-1],
      'atividade_complementar_3' => $dadosRegistro[22-1],
      'atividade_complementar_4' => $dadosRegistro[23-1],
      'atividade_complementar_5' => $dadosRegistro[24-1],
      'atividade_complementar_6' => $dadosRegistro[25-1],
      'aee_braille' => $dadosRegistro[26-1],
      'aee_recurso_optico' => $dadosRegistro[27-1],
      'aee_estrategia_desenvolvimento' => $dadosRegistro[28-1],
      'aee_tecnica_mobilidade' => $dadosRegistro[29-1],
      'aee_libras' => $dadosRegistro[30-1],
      'aee_caa' => $dadosRegistro[31-1],
      'aee_curricular' => $dadosRegistro[32-1],
      'aee_soroban' => $dadosRegistro[33-1],
      'aee_informatica' => $dadosRegistro[34-1],
      'aee_lingua_escrita' => $dadosRegistro[35-1],
      'aee_autonomia' => $dadosRegistro[36-1],
      'etapa_educacenso' => $dadosRegistro[38-1],
      'cod_curso_profissional' => $dadosRegistro[39-1],
    );


    $etapaEnsinoCenso = $dadosRegistro[37-1];
    $codEscola = $this->existeEscola($inepEscola);

    if($codEscola){
      $codTurma = null;
      if(!empty($inepTurma)){
        $codTurma = $this->existeTurma($inepTurma);
      }


      if(!$codTurma){

        $codTurmaTipo = $this->getOrCreateTurmaTipo();
        $codCurso = $this->getOrCreateCurso($etapaEnsinoCenso, $codEscola);
        $codSerie = $this->getOrCreateSerie($etapaEnsinoCenso, $codEscola, $codCurso);

        $turma = new clsPmieducarTurma();
        $turma->ref_cod_instituicao = $this->ref_cod_instituicao;
        $turma->ref_usuario_cad = $this->pessoa_logada;
        $turma->ref_ref_cod_escola = $codEscola;
        $turma->ref_cod_curso = $codCurso;
        $turma->ref_ref_cod_serie = $codSerie;
        $turma->nm_turma = $nomeTurma;
        $turma->sgl_turma = '';
        $turma->max_aluno = 99;
        $turma->ativo = 1;
        $turma->multiseriada = 0;
        $turma->visivel = 1;
        $turma->ref_cod_turma_tipo = $codTurmaTipo;
        $turma->hora_inicial = $horaInicial;
        $turma->hora_final = $horaFinal;
        $turma->ano = $this->ano;
        $turma->tipo_boletim = 1;

        foreach ($camposTurma as $key => $value) {
          $turma->{$key} = $value;
        }
        $codTurma = $turma->cadastra();
        $turma->cod_turma = $codTurma;

        if(!empty($inepTurma)){
          $turma->updateInep($inepTurma);
        }

        foreach ($diasSemana as $key => $diaSemana) {
          $obj = new clsPmieducarTurmaDiaSemana($diaSemana,
              $codTurma, $horaInicial, $horaFinal);

          $obj->cadastra();
        }
      }

      // TODO: componentes
    }
  }

  function getOrCreateTurmaTipo(){
    $codTurmaTipo= null;

    $turmaTipo = new clsPmieducarTurmaTipo();
    $turmaTipos = $turmaTipo->lista();

    if ($turmaTipos) {
      $codTurmaTipo = $turmaTipos[0]['cod_turma_tipo'];
    } else {
      $turmaTipo->ref_usuario_cad = $this->pessoa_logada;
      $turmaTipo->nm_tipo = "Regular";
      $turmaTipo->sgl_turma = "Reg";
      $turmaTipo->ref_cod_instituicao = $this->ref_cod_instituicao;
      $codTurmaTipo = $turmaTipo->cadastra();
    }

    return $codTurmaTipo;
  }

  function getOrCreateSerie($etapaEnsinoCenso, $codEscola, $codCurso){
    $dadosSerie = $this->etapasCenso[$etapaEnsinoCenso];
    $codSerie = null;

    $series = new clsPmieducarSerie();
    $series = $series->lista(null, null, null, $codCurso, null, $dadosSerie['etapa'], null, null, null, null, null, null, 1, $this->ref_cod_instituicao);
    if ($series) {
      $codSerie = $series[0]['cod_serie'];
    } else {
      $serie = new clsPmieducarSerie();
      $serie->ref_usuario_cad = $this->pessoa_logada;
      $serie->ref_cod_curso = $codCurso;
      $serie->nm_serie = $dadosSerie['serie'];
      $serie->etapa_curso = $dadosSerie['etapa'];
      $serie->concluinte = ($dadosSerie['etapa'] == $dadosSerie['etapas']) ? 1 : 0;
      $serie->carga_horaria = 800;
      $serie->dias_letivos = 200;
      $serie->ativo = 1;
      $serie->intervalo = 1;
      $codSerie = $serie->cadastra();
    }

    if ($codEscola) {
      $escolaSerie = new clsPmieducarEscolaSerie();
      $escolaSerie = $escolaSerie->lista($codEscola, $codSerie);
      if (!$escolaSerie) {
        $vinculo = new clsPmieducarEscolaSerie();
        $vinculo->ref_cod_escola = $codEscola;
        $vinculo->ref_cod_serie = $codSerie;
        $vinculo->ref_usuario_cad = $this->pessoa_logada;
        $vinculo->hora_inicial = "07:30:00";
        $vinculo->hora_final = "12:00:00";
        $vinculo->hora_inicio_intervalo = "09:50:00";
        $vinculo->hora_fim_intervalo = "10:20:00";
        $vinculo->cadastra();
      }
    }

    return $codSerie;
  }

  function getOrCreateCurso($etapaEnsinoCenso, $codEscola){
    $dadosCurso = $this->etapasCenso[$etapaEnsinoCenso];
    $codCurso = null;


    $codNivelEnsino = $this->getOrCreateNivelEnsino($nivel);
    $codTipoEnsino = $this->getOrCreateTipoEnsino();

    $cursos = new clsPmieducarCurso();
    $cursos = $cursos->lista(
            null,
            null,
            null,
            $codNivelEnsino,
            $codTipoEnsino,
            null,
            $dadosCurso['nome'],
            null, null, # $str_sgl_curso = NULL, $int_qtd_etapas = NULL,
            null, null, null, # $int_frequencia_minima = NULL, $int_media = NULL, $int_media_exame = NULL,
            null, null, # $int_falta_ch_globalizada = NULL, $int_carga_horaria = NULL,
            null, null, # $str_ato_poder_publico = NULL, $int_edicao_final = NULL,
            null, null, # $str_objetivo_curso = NULL, $str_publico_alvo = NULL,
            null, null, # $date_data_cadastro_ini = NULL, $date_data_cadastro_fim = NULL,
            null, null, # $date_data_exclusao_ini = NULL, $date_data_exclusao_fim = NULL,
            1, null, # $int_ativo = NULL, $int_ref_usuario_exc = NULL,
            $this->ref_cod_instituicao, null # $int_ref_cod_instituicao = NULL, $int_padrao_ano_escolar = NULL,
      );
    if ($cursos) {
        $codCurso = intval($cursos[0]['cod_curso']);
    } else {
        $curso = new clsPmieducarCurso();
        $curso->nm_curso = $dadosCurso['curso'];
        $curso->sgl_curso = substr($dadosCurso['curso'], 0, 15);
        $curso->qtd_etapas = $dadosCurso['etapas'];
        $curso->carga_horaria = 800 * $dadosCurso['etapas'];
        $curso->ativo = 1;
        $curso->ref_cod_nivel_ensino = $codNivelEnsino;
        $curso->ref_cod_tipo_ensino = $codTipoEnsino;
        $curso->ref_cod_instituicao = $this->ref_cod_instituicao;
        $curso->ref_usuario_cad = $this->pessoa_logada;
        $curso->padrao_ano_escolar = 1;
        $curso->multi_seriado = 1;
        $codCurso = $curso->cadastra();
    }

    if ($codEscola) {
      $escolaCurso = new clsPmieducarEscolaCurso();
      $escolaCurso = $escolaCurso->lista($codEscola, $codCurso);
      if (!$escolaCurso) {
        $vinculo = new clsPmieducarEscolaCurso();
        $vinculo->ref_cod_curso = $codCurso;
        $vinculo->ref_cod_escola = $codEscola;
        $vinculo->ref_usuario_cad = $this->pessoa_logada;
        $vinculo->ativo = 1;
        $vinculo->cadastra();
      }
    }


    return $codCurso;
  }

  function getOrCreateTipoEnsino(){
    $codTipoEnsino = $this->getTipoEnsino();

    if(!$codTipoEnsino){
      $tipoEnsino = new clsPmieducarTipoEnsino();
      $tipoEnsino->nm_tipo = "Padrão";
      $tipoEnsino->ativo = 1;
      $tipoEnsino->ref_cod_instituicao = $this->ref_cod_instituicao;
      $tipoEnsino->ref_usuario_cad = $this->pessoa_logada;
      $codTipoEnsino = $tipoEnsino->cadastra();
    }

    return $codTipoEnsino;
  }

  function getTipoEnsino(){
    $sql = "SELECT cod_tipo_ensino
              FROM pmieducar.tipo_ensino
              WHERE ativo = 1
              AND ref_cod_instituicao = {$this->ref_cod_instituicao}
              LIMIT 1";
    return Portabilis_Utils_Database::selectField($sql);
  }

  function getOrCreateNivelEnsino($nivel){
    $codNivelEnsino = $this->getNivelEnsino($nivel);

    if(!$codNivelEnsino){
      $objNivelEnsino = new clsPmieducarNivelEnsino();
      $objNivelEnsino->nm_nivel = $nivel;
      $objNivelEnsino->ref_cod_instituicao = $this->ref_cod_instituicao;
      $objNivelEnsino->ativo = 1;
      $objNivelEnsino->ref_usuario_cad = $this->pessoa_logada;
      $codNivelEnsino = $objNivelEnsino->cadastra();
    }

    return $codNivelEnsino;
  }

  function getNivelEnsino($nivel){
    $sql = "SELECT cod_nivel_ensino
            FROM pmieducar.nivel_ensino
            WHERE ref_cod_instituicao = {$this->ref_cod_instituicao}
            AND nm_nivel ILIKE '{$nivel}'
            AND ativo = 1
            LIMIT 1
    ";

    return Portabilis_Utils_Database::selectField($sql);
  }



  function importaRegistro30($dadosRegistro){

    $inepEscola = $dadosRegistro[2-1];
    $inepServidor = $dadosRegistro[3-1];
    //$codServidor = $dadosRegistro[4-1];
    $nome = $dadosRegistro[5-1];
    $email = $dadosRegistro[6-1];
    $dataNascimento =Portabilis_Date_Utils::brToPgSQL($dadosRegistro[8-1]);
    $sexo = $dadosRegistro[9-1] == "1" ? "M" : "F";
    $racaEducacenso = $dadosRegistro[10-1];
    $nomeMae = $dadosRegistro[12-1];
    $nomePai = $dadosRegistro[13-1];

    if(!is_numeric($inepServidor)){
      return false;
    }
    $codServidor = $this->existeServidor($inepServidor);

    if(!$codServidor){
      $idpesPai = $this->cadastraPessoaFisica($nomePai, null, "M");
      $idpesMae = $this->cadastraPessoaFisica($nomeMae, null, "F");
      $idpesServidor = $this->cadastraPessoaFisica($nome, $dataNascimento, $sexo);

      $codServidor = $this->createServidor($idpesServidor);

      if(!$codServidor){
        return false;
      }

      $this->createServidorEducacenso($codServidor, $inepServidor);
    }
  }

  function createServidorEducacenso($codServidor, $inepServidor){
    $sql = "INSERT INTO modules.educacenso_cod_docente (cod_servidor,cod_docente_inep, fonte, created_at)
                                                  VALUES ($1, $2,'I', 'NOW()')";
    Portabilis_Utils_Database::fetchPreparedQuery($sql, array('params' => array($codServidor, $inepServidor)));
  }

  function createServidor($idpesServidor){
    $servidor = new clsPmieducarServidor();
    $servidor->cod_servidor = $idpesServidor;
    $servidor->ref_cod_instituicao = $this->ref_cod_instituicao;
    $servidor->carga_horaria = 0;

    return $servidor->cadastra();
  }

  function existeServidor($inep){
    $sql = "SELECT cod_servidor
            FROM modules.educacenso_cod_docente
            WHERE cod_docente_inep = {$inep}
    ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function cadastraPessoaFisica($nome, $dataNascimento = null, $sexo = null){
    $idpes = null;
    if(!empty($nome)){
      $pessoa = new clsPessoa_(
        null, $nome, null, null,
        'F', null, null, $email
      );
      $idpes = $pessoa->cadastra();

      $fisica = new clsFisica(
        $idpes,$dataNascimento, $sexo
      );
      $fisica->idpes_cad = $this->pessoa_logada;
      $fisica->cadastra();
    }
    return $idpes;
  }

  function cadastraEnderecoEscola($codEscola, $cep, $logradouro, $enderecoNumero, $complemento, $nomeBairro, $ufIbge, $municipioIbge, $distritoIbge, $localizacao){

    // TODO (Notificar quando endereço não for criado?)

    $escola = new clsPmieducarEscola($codEscola);
    $detEscola = $escola->detalhe();

    $idpesEscola = $detEscola['ref_idpes'];

    if($this->checkEnderecoPessoa($idpesEscola)){
      return false;
    }

    $idmun = $this->getMunicipioByCodIbge($municipioIbge);

    if(!$idmun){
      return false;
    }

    $iddis = $this->getDistritoByCodIbge($distritoIbge);

    if(!$iddis){
      return false;
    }

    $idbai = $this->getOrCreateBairro($idmun, $iddis, $nomeBairro);

    $idlog = $this->getOrCreateLogradouro($logradouro, $idmun);

    if(!$idlog || !$idbai){
      return false;
    }

    $obj = new clsCepLogradouro($cep, $idlog);
    if(!$obj->existe()){
      $obj->cadastra();
    }
    $obj = new clsCepLogradouroBairro($idlog, $cep, $idbai);
    if(!$obj->existe()){
      $obj->cadastra();
    }

    $objEndereco = new clsPessoaEndereco($idpesEscola, $cep, $idlog, $idbai, $enderecoNumero, $complemento);
    $objEndereco->idpes_cad = $this->pessoa_logada;
    $objEndereco->cadastra();
  }

  function getOrCreateLogradouro($logradouro, $idmun){
    $idlog = $this->getLogradouro($logradouro, $idmun);

    if(!$idlog){
      $split = explode(' ', $logradouro, 2);
      $parteLogradouro = isset($split[1]) ? $split[1] : $logradouro;

      // TODO: Verificar forma melhor de verificar o tipo do logradouro na string
      if($split[0] == "RUA"){
        $logradouro = $parteLogradouro;
      }
      $objLogradouro = new clsLogradouro();
      $objLogradouro->idtlog = "RUA";
      $objLogradouro->nome = $logradouro;
      $objLogradouro->idmun = $idmun;
      $objLogradouro->ident_oficial = "N";
      $idlog = $objLogradouro->cadastra();
    }

    return $idlog;
  }

  function getLogradouro($logradouro, $idmun){
    $split = explode(' ', $logradouro, 2);
    $parteLogradouro = isset($split[1]) ? $split[1] : $logradouro;

    $sql = "SELECT idlog
            from public.logradouro
            WHERE (
              nome ILIKE '{$logradouro}'
              OR nome ILIKE '{$parteLogradouro}'
            ) AND idmun = {$idmun}
            limit 1
    ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function getOrCreateBairro($idmun, $iddis, $nomeBairro){
    $idbai = $this->getBairro($idmun, $iddis, $nomeBairro);

    if(!$idbai){
      $bairro = new clsBairro();
      $bairro->idmun = $idmun;
      $bairro->iddis = $iddis;
      $bairro->nome = $nomeBairro;
      $idbai = $bairro->cadastra();
    }

    return $idbai;
  }

  function getBairro($idmun, $iddis, $nomeBairro){
    $sql = "SELECT idbai
              FROM public.bairro
              WHERE idmun = {$idmun}
              AND iddis = {$iddis}
              AND nome ILIKE '{$nomeBairro}%'
    ";
    return Portabilis_Utils_Database::selectField($sql);
  }

  function getDistritoByCodIbge($distritoIbge){
    $sql = "SELECT iddis
              from public.distrito
              where cod_ibge = '{$distritoIbge}'
              limit 1 ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function getMunicipioByCodIbge($municipioIbge){
    $sql = "SELECT idmun
              from public.municipio
              where cod_ibge = '{$municipioIbge}'
              limit 1 ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function checkEnderecoPessoa($idpes){
    $sql = "SELECT idpes from cadastro.endereco_pessoa where idpes = {$idpes} limit 1 ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function atualizaCamposEscolaRegistro00(
    $codEscola, $cargoGestor, $situacao, $latitude,
    $longitude, $codigoOrgaoRegional, $dependenciaAdministrativa,
    $regulamentacao
    ){

    $escola = new clsPmieducarEscola($codEscola);

    $fields = $escola->detalhe();

    foreach ($fields as $key => $value){
      if(property_exists($escola, $key)){
        $escola->{$key} = $value;
      }
    }

    if($cargoGestor){
      $escola->cargo_gestor = $cargoGestor;
    }
    if($situacao){
      $escola->situacao_funcionamento = $situacao;
    }

    if(!empty($latitude)){
      $escola->latitude = $latitude;
    }
    if(!empty($longitude)){
      $escola->longitude = $longitude;
    }
    if($codigoOrgaoRegional){
      $escola->orgao_regional = $codigoOrgaoRegional;
    }
    if($dependenciaAdministrativa){
      $escola->dependencia_administrativa = $dependenciaAdministrativa;
    }
    if($regulamentacao){
      $escola->regulamentacao = $regulamentacao;
    }

    $escola->edita();
  }

  function createEscolaEducacenso($codEscola, $inep){
    $dataMapper = Portabilis_DataMapper_Utils::getDataMapperFor('educacenso', 'escola');

    $data = array(
      'escola'      => $codEscola,
      'escolaInep'  => $inep,
      'fonte'      => 'importador',
      'nomeInep'   => '-',
      'created_at' => 'NOW()',
    );

    $entity = $dataMapper->createNewEntityInstance();
    $entity->setOptions($data);

    $dataMapper->save($entity);
  }

  function createEscola($codEscolaLocalizacao, $codEscolaRedeEnsino, $idpesEscola, $nomeEscola){
    $escola = new clsPmieducarEscola();

    $escola->ref_usuario_cad = $this->pessoa_logada;
    $escola->ref_cod_instituicao = $this->ref_cod_instituicao;
    $escola->ref_cod_escola_localizacao = $codEscolaLocalizacao;
    $escola->ref_cod_escola_rede_ensino = $codEscolaRedeEnsino;
    $escola->ref_idpes = $idpesEscola;
    $escola->sigla = substr($nomeEscola, 0, 5);
    $escola->ativo = 1;

    return $escola->cadastra();
  }

  function getOrCreateRedeDeEnsino(){
    $codEscolaRedeEnsino = $this->getRedeDeEnsino();

    if(!$codEscolaRedeEnsino){
      $rede_ensino = new clsPmieducarEscolaRedeEnsino();
      $rede_ensino->ref_usuario_cad = $this->pessoa_logada;
      $rede_ensino->nm_rede = "Importação Educacenso";
      $rede_ensino->ativo = 1;
      $rede_ensino->ref_cod_instituicao = $this->ref_cod_instituicao;

      $codEscolaRedeEnsino = $rede_ensino->cadastra();
    }
    return $codEscolaRedeEnsino;
  }

  function getOrCreateLocalizacaoEscola($localizacao){
    $localizacaoString = $localizacao == "1" ? "Urbana" : "Rural";

    $codEscolaLocalizacao = $this->getLocalizacaoEscola($localizacaoString);
    if (!$codEscolaLocalizacao) {
      $escolaLocalizacao = new clsPmieducarEscolaLocalizacao();
      $escolaLocalizacao->ref_usuario_cad = $this->pessoa_logada;
      $escolaLocalizacao->nm_localizacao = $localizacaoString;
      $escolaLocalizacao->ativo = 1;
      $escolaLocalizacao->ref_cod_instituicao = $this->ref_cod_instituicao;

      $codEscolaLocalizacao = $escolaLocalizacao->cadastra();
    }
    return $codEscolaLocalizacao;
  }

  function getLocalizacaoEscola($nm_localizacao){
    $sql = "SELECT cod_escola_localizacao
              FROM pmieducar.escola_localizacao
              WHERE nm_localizacao ILIKE '{$nm_localizacao}'
              LIMIT 1 ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function getRedeDeEnsino() {
    $sql = "SELECT cod_escola_rede_ensino FROM pmieducar.escola_rede_ensino LIMIT 1 ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function existePessoa($cpf) {
    $sql = "SELECT idpes
              FROM cadastro.fisica
             WHERE cpf = '{$cpf}' ";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function existeTurma($inep) {
    $sql = "SELECT cod_turma
              FROM modules.educacenso_cod_turma
             WHERE cod_turma_inep = {$inep}";

    return Portabilis_Utils_Database::selectField($sql);
  }

  function existeEscola($inep) {
    $sql = "SELECT cod_escola
              FROM modules.educacenso_cod_escola
             WHERE cod_escola_inep = {$inep}";

    return Portabilis_Utils_Database::selectField($sql);
  }

  private $etapasCenso = array(
      1 => array(
        'curso' => "Educação Infantil",
        'serie' => "Creche (0 a 3 anos)",
        'etapa' => 1,
        'etapas' => 3,
        'nivel' => 'Infantil'
      ),
      2 => array(
        'curso' => "Educação Infantil",
        'serie' => "Pré-escola (4 e 5 anos)",
        'etapa' => 2,
        'etapas' => 3,
        'nivel' => 'Infantil'
      ),
      3 => array(
        'curso' => "Educação Infantil",
        'serie' => "Unificada (0 a 5 anos)",
        'etapa' => 3,
        'etapas' => 3,
        'nivel' => 'Infantil'
      ),
      4 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "1ª Série",
        'etapa' => 1,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      5 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "2ª Série",
        'etapa' => 2,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      6 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "3ª Série",
        'etapa' => 3,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      7 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "4ª Série",
        'etapa' => 4,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      8 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "5ª Série",
        'etapa' => 5,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      9 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "6ª Série",
        'etapa' => 6,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      10 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "7ª Série",
        'etapa' => 7,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      11 => array(
        'curso' => "Ensino Fundamental de 8 anos",
        'serie' => "8ª Série",
        'etapa' => 8,
        'etapas' => 8,
        'nivel' => 'Fundamental'
      ),
      12 => array(
        'curso' => "Ensino Fundamental de 8 anos - Multi",
        'serie' => "Multi",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Fundamental'
      ),
      13 => array(
        'curso' => "Ensino Fundamental de 8 anos - Correção de Fluxo",
        'serie' => "Correção de Fluxo",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Fundamental'
      ),
      14 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "1º Ano",
        'etapa' => 1,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      15 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "2º Ano",
        'etapa' => 2,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      16 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "3º Ano",
        'etapa' => 3,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      17 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "4º Ano",
        'etapa' => 4,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      18 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "5º Ano",
        'etapa' => 5,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      19 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "6º Ano",
        'etapa' => 6,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      20 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "7º Ano",
        'etapa' => 7,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      21 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "8º Ano",
        'etapa' => 8,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      22 => array(
        'curso' => "Ensino Fundamental de 9 anos - Multi",
        'serie' => "Multi",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Fundamental'
      ),
      23 => array(
        'curso' => "Ensino Fundamental de 9 anos - Correção de Fluxo",
        'serie' => "Correção de Fluxo",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Fundamental'
      ),
      24 => array(
        'curso' => "Ensino Fundamental de 8 e 9 anos",
        'serie' => "Multi 8 e 9 anos",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Fundamental'
      ),
      25 => array(
        'curso' => "Ensino Médio",
        'serie' => "1ª Série",
        'etapa' => 1,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      26 => array(
        'curso' => "Ensino Médio",
        'serie' => "2ª Série",
        'etapa' => 2,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      27 => array(
        'curso' => "Ensino Médio",
        'serie' => "3ª Série",
        'etapa' => 3,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      28 => array(
        'curso' => "Ensino Médio",
        'serie' => "4ª Série",
        'etapa' => 4,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      29 => array(
        'curso' => "Ensino Médio Não-seriado",
        'serie' => "Não Seriada",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Médio'
      ),
      30 => array(
        'curso' => "Ensino Médio Integrado",
        'serie' => "Integrado 1ª Série",
        'etapa' => 1,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      31 => array(
        'curso' => "Ensino Médio Integrado",
        'serie' => "Integrado 2ª Série",
        'etapa' => 2,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      32 => array(
        'curso' => "Ensino Médio Integrado",
        'serie' => "Integrado 3ª Série",
        'etapa' => 3,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      33 => array(
        'curso' => "Ensino Médio Integrado",
        'serie' => "Integrado 4ª Série",
        'etapa' => 4,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
34 => array('curso' => "Ensino Médio Integrado Não-Seriado",
        'serie' => "Integrado Não Seriada",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Médio'
      ),
      35 => array(
        'curso' => "Ensino Médio - Magistério",
        'serie' => "Normal/Magistério 1ª Série",
        'etapa' => 1,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      36 => array(
        'curso' => "Ensino Médio - Magistério",
        'serie' => "Normal/Magistério 2ª Série",
        'etapa' => 2,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      37 => array(
        'curso' => "Ensino Médio - Magistério",
        'serie' => "Normal/Magistério 3ª Série",
        'etapa' => 3,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      38 => array(
        'curso' => "Ensino Médio - Magistério",
        'serie' => "Normal/Magistério 4ª Série",
        'etapa' => 4,
        'etapas' => 4,
        'nivel' => 'Médio'
      ),
      39 => array(
        'curso' => "Educação Profissional (Concomitante)",
        'serie' => "Não-seriado",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Médio'
      ),
      40 => array(
        'curso' => "Educação Profissional (Subseqüente)",
        'serie' => "Não-seriado",
        'etapa' => 1,
        'etapas' => 1,
        'nivel' => 'Médio'
      ),
      41 => array(
        'curso' => "Ensino Fundamental de 9 anos",
        'serie' => "9º Ano",
        'etapa' => 9,
        'etapas' => 9,
        'nivel' => 'Fundamental'
      ),
      43 => array(
        'curso' => "EJA Presencial",
        'serie' => "Anos iniciais",
        'etapa' => 1,
        'etapas' => 2,
        'nivel' => 'Médio'
      ),
      44 => array(
        'curso' => "EJA Presencial",
        'serie' => "Anos finais",
        'etapa' => 2,
        'etapas' => 2,
        'nivel' => 'Médio'
      )
    );
}
// Instancia objeto de página
$pagina = new clsIndexBase();

// Instancia objeto de conteúdo
$miolo = new indice();

// Atribui o conteúdo à  página
$pagina->addForm($miolo);

// Gera o código HTML
$pagina->MakeAll();
?>
