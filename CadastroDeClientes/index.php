<?php
/**
 * Clientes
 *
 * Delivelton Teixeira Rodrigues
 */
     
// Verificar se foi enviando dados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_input(INPUT_POST, 'id');
    $nome = filter_input(INPUT_POST, 'nome');
    $cpf_cnpj = filter_input(INPUT_POST, 'cpf_cnpj');
    $email = filter_input(INPUT_POST, 'email');
    $telefone = filter_input(INPUT_POST, 'telefone');
    $pais = filter_input(INPUT_POST, 'pais');
    $estado = filter_input(INPUT_POST, 'estado');
    $cidade = filter_input(INPUT_POST, 'cidade');
    $logradouro = filter_input(INPUT_POST, 'logradouro');
    $numero = filter_input(INPUT_POST, 'numero');
    $cep = filter_input(INPUT_POST, 'cep');
} else if (!isset($id)) {
// Checando id
    $id = (isset($_GET["id"]) && $_GET["id"] != null) ? $_GET["id"] : "";
}

try {
    $conexao = new PDO("mysql:host=localhost;dbname=clientes", "root", "");
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->exec("set names utf8");
} catch (PDOException $erro) {
    echo "<p class=\"bg-danger\">Erro conexão:" . $erro->getMessage() . "</p>";
}

// Bloco If que Salva os dados no Banco - atua como Create e Update
if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "save" && $nome != "") {
    try {
 
        if ($id != "") {
            $stmtEndereco = $conexao->prepare("UPDATE endereco SET pais=?, estado=?, cidade=?, logradouro=?, numero=?, cep=? WHERE id = (select cliente.endereco from cliente WHERE id = ?)");
            $stmtEndereco->bindParam(7, $id);
        } else {
            $stmtEndereco = $conexao->prepare("INSERT INTO endereco (pais, estado, cidade, logradouro, numero, cep) VALUES (?, ?, ?, ?, ?, ?)");
        }
        
        $stmtEndereco->bindParam(1, $pais);
        $stmtEndereco->bindParam(2, $estado);
        $stmtEndereco->bindParam(3, $cidade);
        $stmtEndereco->bindParam(4, $logradouro);
        $stmtEndereco->bindParam(5, $numero);
        $stmtEndereco->bindParam(6, $cep);


        if ($id != "") {
            $stmt = $conexao->prepare("UPDATE cliente SET nome=?, cpf_cnpj=?, email=?, telefone=?, WHERE id = ?");
            $stmt->bindParam(5, $id);
        } else {
            $stmt = $conexao->prepare("INSERT INTO cliente (nome, cpf_cnpj, email, telefone) VALUES (?, ?, ?, ?)");
        }
    
        $stmt->bindParam(1, $nome);
        $stmt->bindParam(2, $cpf_cnpj);
        $stmt->bindParam(3, $email);
        $stmt->bindParam(4, $telefone);

        if ($stmtEndereco->execute()) {
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo "<p class=\"bg-success\">Dados cadastrados com sucesso!</p>";
                    $id = null;
                    $nome = null;
                    $cpf_cnpj = null;
                    $email = null;
                    $telefone = null;
                    $pais = null;
                    $estado = null;
                    $cidade = null;
                    $logradouro = null;
                    $cep = null;
                    $numero = null;
                } else {
                    echo "<p class=\"bg-danger\">Erro ao concluir cadastro</p>";
                }
            } else {
                echo "<p class=\"bg-danger\">Erro: cadastro não realizado</p>";
            }
            if ($id == "") {
                $stmtAtualizaEndereco = $conexao->prepare("UPDATE cliente SET endereco=(select endereco.id from endereco order by id desc LIMIT 1) where id=(select cliente.id from cliente order by id desc LIMIT 1)");
                $stmtAtualizaEndereco->execute();
            } 
        }
        

    } catch (PDOException $erro) {
        echo "<p class=\"bg-danger\">Erro: " . $erro->getMessage() . "</p>";
    }
}

// Atualizar
if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "upd" && $id != "") {
    try {
        $stmt = $conexao->prepare("SELECT * FROM cliente WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $rs = $stmt->fetch(PDO::FETCH_OBJ);
            $id = $rs->id;
            $nome = $rs->nome;
            $cpf_cnpj = $rs->cpf_cnpj;
            $email = $rs->email;
            $telefone = $rs->telefone;

            $stmtEndereco = $conexao->prepare("SELECT * FROM endereco WHERE id=?");
            $stmtEndereco->bindParam(1, $rs->endereco, PDO::PARAM_INT);
            if ($stmtEndereco->execute()) {
                $ender = $stmtEndereco->fetch(PDO::FETCH_OBJ);
                $pais = $ender->pais;
                $estado = $ender->estado;
                $cidade = $ender->cidade;
                $logradouro = $ender->logradouro;
                $cep = $ender->cep;
                $numero = $ender->numero;

            } else {
                echo "<p class=\"bg-danger\">Erro ao concluir atualização</p>";
            }
        } else {
            echo "<p class=\"bg-danger\">Erro ao concluir atualização</p>";
        }

    } catch (PDOException $erro) {
        echo "<p class=\"bg-danger\">Erro: " . $erro->getMessage() . "</p>";
    }
}

if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "del" && $id != "") {
    try {
        $stmt = $conexao->prepare("DELETE FROM cliente WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<p class=\"bg-success\">Cliente excluído com êxito</p>";
            $id = null;
        } else {
            echo "<p class=\"bg-danger\">Erro ao excluir cliente</p>";
        }
    } catch (PDOException $erro) {
        echo "<p class=\"bg-danger\">Erro: " . $erro->getMessage() . "</a>";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge" >
        <title>Lista de clientes</title>
        <link href="assets/css/bootstrap.css" type="text/css" rel="stylesheet" />
        <script src="assets/js/bootstrap.js" type="text/javascript" ></script>
    </head>
    <body>
        <div class="container">
            <header class="row">
                <br />
            </header>
            <article>
                <div class="row">
                    <form action="?act=save" method="POST" name="form1" class="form-horizontal" >
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <span class="panel-title">Cliente</span>
                            </div>
                            <div class="panel-body">

                                <input type="hidden" name="id" value="<?php
                                // Preenche o id
                                echo (isset($id) && ($id != null || $id != "")) ? $id : '';
                                ?>" />
                                <div class="form-group">
                                    <label for="nome" class="col-sm-1 control-label">Nome:</label>
                                    <div class="col-md-5">
                                        <input type="text" name="nome" value="<?php
                                        echo (isset($nome) && ($nome != null || $nome != "")) ? $nome : '';

                                        ?>" class="form-control"/>
                                    </div>
                                    <label for="cpf_cnpj" class="col-sm-1 control-label">cpf/cnpj:</label>
                                    <div class="col-md-4">
                                        <input type="text" name="cpf_cnpj" value="<?php
                                        // Preenche o CPF ou CNPJ
                                        echo (isset($cpf_cnpj) && ($cpf_cnpj != null || $cpf_cnpj != "")) ? $cpf_cnpj : '';

                                        ?>" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="col-sm-1 control-label">Email:</label>
                                    <div class="col-md-4">
                                        <input type="email" name="email" value="<?php
                                        // Preenche o email
                                        echo (isset($email) && ($email != null || $email != "")) ? $email : '';

                                        ?>" class="form-control" />
                                    </div>
                                    <label for="telefone" class="col-sm-2 control-label">Telefone:</label>
                                    <div class="col-md-4">
                                        <input type="text" name="telefone" value="<?php
                                        // Preenche o telefone
                                        echo (isset($telefone) && ($telefone != null || $telefone != "")) ? $telefone : '';
                                        ?>" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pais" class="col-sm-1 control-label">País:</label>
                                    <div class="col-md-4">
                                        <input type="text" name="pais" value="<?php
                                        // Preenche o pais
                                            echo (isset($pais) && ($pais != null || $pais != "")) ? $pais : '';
                                        ?>" class="form-control" />
                                    </div>
                                    <label for="estado" class="col-sm-2 control-label">Estado:</label>
                                    <div class="col-md-2">
                                        <input type="text" name="estado" value="<?php
                                        // Preenche o estado
                                        echo (isset($estado) && ($estado != null || $estado != "")) ? $estado : '';

                                        ?>" class="form-control" />
                                    </div>
                                    <label for="logradouro" class="col-sm-1 control-label">Logradouro:</label>
                                    <div class="col-md-2">
                                        <input type="text" name="logradouro" <?php
                                        // Preenche o logradouro"
                                        echo (isset($logradouro) && ($logradouro != null || $logradouro != "")) ? $logradouro : '';

                                        ?> class="form-control" />
                                    </div>
                                    </div>
                                <div class="form-group">
                                    <label for="cidade" class="col-sm-1 control-label">Cidade:</label>
                                    <div class="col-md-4">
                                        <input type="text" name="cidade" value="<?php
                                        // Preenche a cidade
                                        echo (isset($cidade) && ($cidade != null || $cidade != "")) ? $cidade : '';

                                        ?>" class="form-control" />
                                    </div>
 
                                    <label for="cep" class="col-sm-2 control-label">Cep:</label>
                                    <div class="col-md-2">
                                        <input type="text" name="cep" value="<?php
                                        // Preenche o cep
                                        echo (isset($cep) && ($cep != null || $cep != "")) ? $cep : '';
                                        ?>" class="form-control" />
                                    </div>
                                    <label for="numero" class="col-sm-1 control-label">Número:</label>
                                    <div class="col-md-2">
                                        <input type="text" name="numero" value="<?php
                                        // Preenche o numero"
                                        echo (isset($numero) && ($numero != null || $numero != "")) ? $numero : '';

                                        ?>" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="clearfix">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-primary" /><span class="glyphicon glyphicon-ok"></span> salvar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="panel panel-default">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>nome</th>
                                    <th>cpf_cnpj</th>
                                    <th>telefone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                
                                try {
                                    $stmt = $conexao->prepare("SELECT * FROM cliente");
                                    if ($stmt->execute()) {
                                        while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {

                                            ?><tr>
                                                <td><?php echo $rs->nome; ?></td>
                                                <td><?php echo $rs->cpf_cnpj; ?></td>                                
                                                <td><?php echo $rs->telefone; ?></td>                                         
                                                <td><center>
                                            <a href="?act=upd&id=<?php echo $rs->id; ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-pencil"></span> Editar</a>
                                            <a href="?act=del&id=<?php echo $rs->id; ?>" class="btn btn-danger btn-xs" ><span class="glyphicon glyphicon-remove"></span> Excluir</a>
                                        </center>
                                        </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "Erro: Não foi possível recuperar os dados do banco de dados";
                                }
                            } catch (PDOException $erro) {
                                echo "Erro: " . $erro->getMessage();
                            }

                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </div>
    </body>
</html>
