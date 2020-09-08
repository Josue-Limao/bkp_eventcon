<?php 
//------------------------------------------------------------------------
/**
  * - @Versão 2.0
  |
  * - @package BKP files And SQL
  |
  * - @author Josué Queiroz <josuestz5@gmail.com>
  |
  * - @Codigo abaixo faz bkp do SQL e do FTP
  |
  * - @Para automatizar ainda mais, coloque esse codigo em um rotina (Cron/Job)
  |
  * - @Email de contato: josuestz5@gmail.com
  |
  * - @license https://4ind.org/
  |
  * - @link https://4ind.org/
*/


// -------------------------- PROGRAMAÇÃO FEITA PARA ABHH --------------------------
// 1) zip sql's
// 2) zip files's
// 3) zip -> sql.files.zip
// 4) send -> All.Zip 
// 5) Cron foi definidi como a cada 15 dias, sendo um dia 15 e o proximo dia 28
// ---------------------------------------------------------------------------------


ini_set('max_execution_time', 7200); //7200 seconds = 2 Hora (60*60*(hora))



// ********** Referencia do dia ************/
$hoje = date("Y-m-d");



//--------------------------------------------------------___________------------------------------------------------------------
//-------------------------------------------------------- BKP DO SQL -----------------------------------------------------------
//----------------------------------------------------- SETANDO O BANCO ---------------------------------------------------------

$mysqlUserName      = "eventc01";
$mysqlPassword      = "&JoFO6c@(51@";
$mysqlHostName      = "";
$DbName             = "eventc01_bd";
$bkp_name           = "Inscritos_congresso.sql";
$backup_name        = "mybackup.sql";
$tables             = array("asset_branch", "asset_category", "asset_products", "asset_user", "login", "message", "replace_app");

Export_Database($mysqlHostName,$mysqlUserName,$mysqlPassword,$DbName,$bkp_name,  $tables=false, $backup_name=false );

//----------------------------------------------------- SETANDO O BANCO ---------------------------------------------------------

$mysqlUserName      = "eventc01";
$mysqlPassword      = "&JoFO6c@(51@";
$mysqlHostName      = "";
$DbName             = "eventc01_submissao";
$bkp_name           = "Submissao_cadastro.sql";
$backup_name        = "mybackup.sql";
$tables             = array("asset_branch", "asset_category", "asset_products", "asset_user", "login", "message", "replace_app");

Export_Database($mysqlHostName,$mysqlUserName,$mysqlPassword,$DbName,$bkp_name,  $tables=false, $backup_name=false );



//---------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------[ Fazendo a conexao e separando o tabela ]------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------


function Export_Database($host,$user,$pass,$name,$bkp_name,  $tables=false, $backup_name=false ){
  $mysqli = new mysqli($host,$user,$pass,$name);
  $mysqli->select_db($name);
  $mysqli->query("SET NAMES 'utf8'");

  $queryTables    = $mysqli->query('SHOW TABLES');
  while($row = $queryTables->fetch_row()){
    $target_tables[] = $row[0];
  }
  if($tables !== false)
  {
    $target_tables = array_intersect( $target_tables, $tables);
  }
  foreach($target_tables as $table){
    $result         =   $mysqli->query('SELECT * FROM '.$table);
    $fields_amount  =   $result->field_count;
    $rows_num=$mysqli->affected_rows;
    $res            =   $mysqli->query('SHOW CREATE TABLE '.$table);
    $TableMLine     =   $res->fetch_row();
    $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

    for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0)
    {
      while($row = $result->fetch_row()){ //regra de 3 par a para definir que o tatal é igual a 100% e inicio o ciclo do while
        if ($st_counter%100 == 0 || $st_counter == 0 )
        {
          $content .= "\nINSERT INTO ".$table." VALUES";
        }
        $content .= "\n(";
        for($j=0; $j<$fields_amount; $j++)
        {
          $row[$j] = str_replace('"', "'", str_replace("\n","\\n", addslashes($row[$j]) ));
          if (isset($row[$j]))
          {
            $content .= '"'.$row[$j].'"' ;
          }
          else
          {
            $content .= '""';
          }
          if ($j<($fields_amount-1))
          {
            $content.= ',';
          }
        }
        $content .=")";
                   //faço a contagem para que seja exportado 100% da tabela
        if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num)
        {
          $content .= ";";
        }
        else
        {
          $content .= ",";
        }
        $st_counter=$st_counter+1;
      }
    } $content .="\n\n\n";
  }
  $hoje = date("Y-m-d");
  $backup_name = $backup_name ? $backup_name : $name."_(".$hoje.")_.sql";

//------------------ Esse codigo abaixo salva o arquivo no FTP com a extenssão .sql, atualmente Até o momento do Zip! -------------------
// header("Pragma: no-cache");
// Não expira
  header("Expires: 0");
// E aqui geramos o arquivo com os dados mencionados acima!
  $fp = fopen("$bkp_name", "a"); 
// seta os dados e cabecalhos
  $escreve = fwrite($fp, $header."\n".$content); 
// Fecha o arquivo  
  $bd_zip = fclose($fp);
//--------------------------------------------------------------------------------------------------------------------------------------!
//----------------------------- Esse codigo abaixo salva o arquivo no FTP com a extenssão SQL.ZIP

// Inicia a instância ZipArchive
  $zip = new ZipArchive;
// Cria um novo arquivo .zip chamado minhas_fotos.zip
  $zip->open($bkp_name.".zip", ZipArchive::CREATE);
// Adiciona um arquivo à pasta
  $zip->addFile("$backup_name","$backup_name$bkp_name");
// Fecha a pasta e salva o arquivo
  $zip->close();
}


$nome_sql1 = "Inscritos_congresso.zip";
$arquivio_sql1 = "Inscritos_congresso.sql";

$nome_sql2 = "Submissao_cadastro.zip";
$arquivio_sql2 = "Submissao_cadastro.sql";

//-------------------------------------------------------------------------------------------------------------------------------
//-------------------------------------------------------- BKP DO FTP------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------


// Apaga o backup anterior para que ele não seja compactado junto com o atual.
if (file_exists($arquivo)) unlink(realpath($arquivo)); 

// diretório que será compactado
$teste = 'BKP_FILES_TESTE_'.$hoje;
$arquivo = $teste.'.zip';
$diretorio = "../pasta_bkp_teste/";  
$Pasta     = $arquivo;
CriarZip($diretorio, $Pasta, $arquivo);


// // diretório que será compactado
// $nome_residencia = 'BKP_FILES_RESIDENCIA_'.$hoje;
// $arquivo = $nome_residencia.'.zip';
// $diretorio = "../../residencia/"; 
// $Pasta     = $arquivo;
// CriarZip($diretorio, $Pasta, $arquivo);


// // diretório que será compactado
// $nome_acreditacao = 'BKP_FILES_ACREDITACAO_'.$hoje;
// $arquivo = $nome_acreditacao.'.zip';
// $diretorio = "../../acreditacao/";  
// $Pasta     = $arquivo;
// CriarZip($diretorio, $Pasta, $arquivo);



function CriarZip($diretorio, $Pasta, $arquivo){

  $rootPath = realpath($diretorio);
// Inicia o Módulo ZipArchive do PHP
  $zip = new ZipArchive();
  $zip->open($arquivo, ZipArchive::CREATE | ZipArchive::OVERWRITE);
// Compactação de subpastas
  $files = new RecursiveIteratorIterator(
   new RecursiveDirectoryIterator($rootPath),
   RecursiveIteratorIterator::LEAVES_ONLY
 );

// Varre todos os arquivos da pasta
  foreach ($files as $name => $file)
  {
   if (!$file->isDir())
   {
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($rootPath) + 1);
// Adiciona os arquivos no pacote Zip.
    $zip->addFile($filePath, $relativePath);
  }

}

// Encerra a criação do pacote .Zip
$zip->close();

   $Pasta = $Pasta.'.zip'; // define o nome do pacote Zip gerado na 9
   if(isset($Pasta) && file_exists($Pasta)){ // faz o teste se a variavel não esta vazia e se o arquivo realmente existe
      switch(strtolower(substr(strrchr(basename($Pasta),"."),1))){ // verifica a extensão do arquivo para pegar o tipo
      	case "pdf": $tipo="application/pdf"; break;
      	case "exe": $tipo="application/octet-stream"; break;
      	case "zip": $tipo="application/zip"; break;
      	case "doc": $tipo="application/msword"; break;
      	case "xls": $tipo="application/vnd.ms-excel"; break;
      	case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
      	case "gif": $tipo="image/gif"; break;
      	case "png": $tipo="image/png"; break;
      	case "jpg": $tipo="image/jpg"; break;
      	case "mp3": $tipo="audio/mpeg"; break;
         case "php": // deixar vazio por seurança
         case "htm": // deixar vazio por seurança
         case "html": // deixar vazio por seurança
       }
     }
   }

//-------------------------------- Função para baixar o arquivo gerado por navegador --------------------------------------------

// Baixa somente o arquivo do ftp não o banco de dados SQL

      // header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
      // header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
      // header("Content-Disposition: attachment; filename=".basename($arquivo)); // informa ao navegador que é tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
      // readfile($arquivo); // lê o arquivo
      // exit; // aborta pós-ações
//-------------------------------------------------------------------------------------------------------------------------------






//-------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------- NOTIFICAÇÃO DE BKP --------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------





   //Seu Email
   $to = "josue@limaocravo.net";
   //$to = "josue@limaocravo.net, rafa@limaocravo.net, junior@limaocravo.net";

   //Titulo do email com data do BKP
   $dia_referente = date('d/m/Y');
   $subject = "Backup $dia_referente";

   //corpo do email
   $body = "
   <table style='background-image: url(https://sistema.limaocravo.srv.br/images/cubes.png);width: 100%;'>
   <tbody>
   <tr>
   <td>
   <center>
   <table style='margin:10px auto;max-width:100%;width:600'>
   <tbody>
   <tr>
   <td width='600' align='right' style='font-size:12px;font-style:italic;opacity:0.5;text-align:right'>
   </td>
   </tr>
   <tr>
   <td style='text-align:center'>
   <center>
   </center>
   </td>
   </tr>
   </tbody>
   </table>
   <table style='margin:0 auto;max-width:100%;width:600px'>
   <tbody>
   <tr>
   <td>
   <table bgcolor='white' border='0' cellpadding='0' cellspacing='0' style='background:white;border-radius:8px;border:0;margin:10px auto;width:100%'>
   <tbody>
   <tr>
   <td>
   &nbsp; <font color='#444444'>
   </font>
   <table border='0' cellpadding='35' cellspacing='0'>
   <tbody>
   <tr>
   <td align='center' style='color:#444444;font-size:18px;line-height:24px'>
   <font color='#444444'>
   <a style='text-decoration:none;color:#444444'>
   <img height='380' alt='Residentes' align='center' style='border-radius:6px;margin:0 auto' src='https://sistema.limaocravo.srv.br/images/seguro.gif'>
   </a>
   </font>
   <center>
   <p style='color:#444444;text-align:center;line-height:28px;font-size:26px'>
   <strong>
   </strong>
   </p>
   <p style='text-decoration:none;color:#444444'>
   <strong>
   <font color='#444444'>
   Olá equipe de TI, estou aqui fazendo nada então pensei, vou fazer um BKP de boas né! vlw é nóis! <br>

   <br></font>
   </strong>
   </p>
   <strong>
   </strong>
   <p>
   </p>
   <p style='color:#444444;text-align:center;line-height:28px;font-size:20px'>
   </p>
   <p style='text-decoration:none;color:#444444'>
   <font color='#444444'>
   <br>
   Faça da sua vida mais 'Responsivo'
   - Dica para Programadores de Sistemas.

   <small>By: Luana Monteiro</small>
   <br>
   <br>
   <small>Backup gerado 1 vez ao dia!</small>
   <br>
   <hr>
   Bkp FTP: <br> <b> FTP's </b><br><br>
   Bkp SQL: <br> <b> Residencia.sql </b>
   Bkp SQL: <br> <b> Acreditacao.sql </b>
   Bkp SQL: <br> <b> Choosing.sql </b>
   Bkp SQL: <br> <b> Sangue_Jovem.sql </b>

   <br>
   <br>
   <center>
   <table border='0' cellpadding='14' cellspacing='0' style='background:#00af2e;border-radius:6px;color:#ffffff;display:inline-block;font-size:20px;font-weight:bold;line-height:24px;margin:0px auto;text-align:center'>
   <tbody>
   <tr>
   <td align='center' style='vertical-align:middle'>
   <font color='#444444'>
   <a href='http://abhh.com.br/files_bkp/arquivos_baixados/SQL_AND_FILES_".$hoje.".zip' style='text-decoration:none;padding:0 8px'>
   <font color='white'>
   Disponível Download!</font>
   </a>
   </font>
   </td>
   </tr>
   </tbody>
   </table>
   </center>
   </a>
   <br>
   <br>
   </p>
   <p>
   </p>
   </center>


   </td>
   </tr>
   </tbody>
   </table>
   </td>
   </tr>
   </tbody>
   </table>
   </td>
   </tr>
   <tr>
   <td>
   <center>
   <a href='https://limaocravo.blog/'>
   <p style='text-decoration:none;color: #fbaf31;'>
   <strong>
   <font style='font-size: 14px;'>Limão-Tec<br></font>
   </strong>
   </p>
   </a>
   </center>
   </td>
   </tr>
   </tbody>
   </table>
   </center>
   </td>
   </tr>
   </tbody>
   </table>
   ";


   //cabeçalhos
   $headers .= "Content-type: text/html; charset=UTF8 \r\n";
   $headers .= "From: Backup<bkp@abhh.com.br/>\r\n";
   $headers .= "Reply-To: $email\r\n";
   $headers .= "Return-path: bkp@abhh.com.br/";


   // Send email | Dispara email  
   //mail($to, $subject, $body, $headers);



   $hoje = date('Y-m-d');

//--------------------------------------------------------------------------------------------------------------------------------
//------------------------- Esse codigo abaixo salva todos as informações geradas acima em UM unico zip! ------------------------- 
//--------------------------------------------------------------------------------------------------------------------------------
   
// Inicia a instância ZipArchive
   $zip = new ZipArchive;

// Cria um novo arquivo .zip chamado minhas_fotos.zip
   $zip->open("ALL_SQL_AND_FILES_".$hoje.".zip", ZipArchive::CREATE);

// Adiciona um arquivo à pasta
   $zip->addFile("$nome_sql1", "$nome_sql1");
   $zip->addFile("$nome_sql2", "$nome_sql2");
   $zip->addFile("$teste.zip", "$teste.zip");

// Fecha a pasta e salva o arquivo
   $zip->close();



//------------ Excluir arquivo gerados antes do zip Total ---------

   $diretorio = "./";
   unlink ($diretorio."$teste.zip");
   unlink ($diretorio.$nome_sql1);
   unlink ($diretorio.$nome_sql2);
   unlink ($diretorio.$arquivio_sql1);
   unlink ($diretorio.$arquivio_sql2);

//------------------------------------------------------------------
   ?>