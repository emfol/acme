<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Formulário de Edição de Produto</title>
		<!-- Stylesheets -->
		<link rel="stylesheet" type="text/css" media="all" href="resources/css/default.css" />
	</head>
	<body>

		<h1>Formulário de Edição de Produto</h1>

		<?php if ( ! empty($message) ): ?>
		<div class="notification">
			<p><?php echo $message; ?></p>
		</div>
		<?php endif; ?>

		<form method="post" action="?c=Products&amp;a=product&amp;pid=<?php echo $pid ?>">
		<table cellpadding="4" cellspacing="0" border="1">
			<tr>
				<th class="field">Nome</th>
				<td>
					<?php if (is_object($product)): ?>
					<input type="text" name="title" size="50" maxlength="150" value="<?php echo htmlspecialchars($product->getTitle(), ENT_COMPAT, 'UTF-8') ?>" />
					<?php else: ?>
					<input type="text" name="title" size="50" maxlength="150" />
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="field">Código de Barras</th>
				<td>
					<?php if (is_object($product)): ?>
					<input type="text" name="barcode" size="50" maxlength="50" value="<?php echo htmlspecialchars($product->getBarcode(), ENT_COMPAT, 'UTF-8') ?>" />
					<?php else: ?>
					<input type="text" name="barcode" size="50" maxlength="50" />
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="field">Modelo</th>
				<td>
					<?php if (is_object($product)): ?>
					<input type="text" name="model" size="30" maxlength="50" value="<?php echo htmlspecialchars($product->getModel(), ENT_COMPAT, 'UTF-8') ?>" />
					<?php else: ?>
					<input type="text" name="model" size="30" maxlength="50" />
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="field">Descrição</th>
				<td>
					<?php if (is_object($product)): ?>
					<textarea name="description" rows="5" cols="50"><?php echo htmlspecialchars($product->getDescription(), ENT_COMPAT, 'UTF-8') ?></textarea>
					<?php else: ?>
					<textarea name="description" rows="5" cols="50"></textarea>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="field">Status</th>
				<td>
					<?php $status = is_object($product) ? $product->getStatus() : 0; ?>
					<select name="status">
						<option value="0"<?php if ($status == 0) echo ' selected="selected"' ?>></option>
						<option value="1"<?php if ($status == 1) echo ' selected="selected"' ?>>Em Avaliação</option>
						<option value="2"<?php if ($status == 2) echo ' selected="selected"' ?>>Em Produção</option>
						<option value="3"<?php if ($status == 3) echo ' selected="selected"' ?>>Descontinuado</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" value="Salvar" />
				</td>
			</tr>
		</table>
		<a href="?c=Products">Voltar</a>
		</form>

	</body>
</html>