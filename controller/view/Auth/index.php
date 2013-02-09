<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Autenticação</title>
		<!-- Stylesheets -->
		<link rel="stylesheet" type="text/css" media="all" href="resources/css/default.css" />
	</head>
	<body>

		<?php if ( ! empty($message) ): ?>
		<div class="notification">
			<p><?php echo $message; ?></p>
		</div>
		<?php endif; ?>

		<form method="post">
			<table cellpadding="4" cellspacing="0" border="1">
				<tr>
					<th class="field">Usuário</th>
					<td><input type="text" name="username" size="20" maxlength="20" /></td>
				</tr>
				<tr>
					<th class="field">Senha</th>
					<td><input type="password" name="password" size="20" maxlength="20" /></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="Login" /></td>
				</tr>
			</table>
		</form>

	</body>
</html>
