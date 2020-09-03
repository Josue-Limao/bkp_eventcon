<?php require("header.php"); ?>

<div class="page-wrapper">
  <div class="container-fluid">    
   <form method="get" action="avaliacao-cadastrar-etapa2.php">
    <div class="row">                

      <div class="col-lg-12 col-xlg-12 col-md-12">
        <div class="card">
          <div class="card-block">   
           <div class="col-md-12">                                      

            <hr class="hr-text" data-content="Nova avaliação">

          </div>

          <div class="col-md-12">
            <label for="titulo">Escolha o curso para criar a avaliação</label>                                    
            <select name="id_curso" class="form-control">
              <?php 
              $dcursos = mysqli_query($con, "SELECT * FROM tbl_cursos");
              while($dados = mysqli_fetch_assoc($dcursos)){

                echo ('<option value="'.$dados["nomedocurso"].'">'.$dados["nomedocurso"].'</option>');

              }
              ?>

            </select>
          </div>
          

          <div class="col-sm-12 mt-5">
            <button type="submit" class="btn btn-success">ETAPA 1/2 ></button>
          </div>
        </div>


      </div>
    </div>
  </div>
</form>
</div>
</div>






<?php require("footer.php"); ?>
