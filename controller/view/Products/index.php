<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Produtos</title>
		<!-- Stylesheets -->
		<link rel="stylesheet" type="text/css" media="all" href="resources/css/default.css" />
	</head>
	<body>
		<h1>Lista de Produtos Cadastrados</h1>
		<form method="get">
			<table>
				<tr>
					<td>Parâmetro</td>
					<td><input type="text" name="param" size="30" maxlength="30" value="<?php echo htmlspecialchars($info['parameter'], ENT_COMPAT, 'UTF-8') ?>" /></td>
					<td><lable><input type="radio" name="field" value="title" <?php if ($info['field'] != 'barcode') echo 'checked="checked"' ?> /> Nome</label></td>
					<td><lable><input type="radio" name="field" value="barcode" <?php if ($info['field'] == 'barcode') echo 'checked="checked"' ?> /> Código de Barras</label></td>
				</tr>
				<tr>
					<td>Limite</td>
					<td><input type="number" name="limit" min="1" max="50" value="<?php echo $info['limit'] ?>" /></td>
					<td>Página</td>
					<td><input type="number" name="page" min="1" value="<?php echo $info['page'] ?>" /></td>
				</tr>
				<tr>
					<td colspan="4">
						<input type="hidden" name="c" value="Products" />
						<input type="submit" value="Pesquisar" />
					</td>
				</tr>
			</table>
		</form>
		<table cellpadding="4" cellspacing="0" border="1">
			<thead>
				<tr>
					<th>ID</th>
					<th>Nome</th>
					<th>Código de Barras</th>
					<th>Modelo</th>
					<th>Status</th>
					<th>Data de Criação</th>
					<th>Opções</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6" align="right"><?php printf('Exibindo %d de %d %s', count($products), $info['total'], $info['total'] > 1 ? 'itens' : 'item') ?></td>
					<td><a href="?c=Products&amp;a=product&amp;">Novo</a></td>
				</tr>
			</tfoot>
			<tbody>
				<?php $status = array('', 'Em Avaliação', 'Em Produção', 'Descontinuado'); ?>
				<?php if (empty($products)): ?>
				<tr>
					<td colspan="7">Nenhum registro encontrado.</td>
				</tr>
				<?php else: ?>
				<?php foreach ($products as $p): ?>
				<?php $s = $p->getStatus() ?>
				<?php $pid = $p->getProductId() ?>
				<tr>
					<td><?php echo $pid ?></td>
					<td><?php echo htmlspecialchars($p->getTitle(), ENT_COMPAT, 'UTF-8') ?></td>
					<td><?php echo htmlspecialchars($p->getBarcode(), ENT_COMPAT, 'UTF-8') ?></td>
					<td><?php echo htmlspecialchars($p->getModel(), ENT_COMPAT, 'UTF-8') ?></td>
					<td><?php echo array_key_exists($s, $status) ? $status[$s] : '' ?></td>
					<td><?php echo date('d/m/Y', $p->getCreationTime()) ?></td>
					<td>
						<a href="?c=Products&amp;a=product&amp;pid=<?php echo $pid ?>">Editar</a>
						<a href="?c=Products&amp;a=delete&amp;pid=<?php echo $pid ?>">Apagar</a>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</body>
</html>