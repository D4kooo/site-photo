<?php require_once 'views/includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/admin.css">

<div class="container mt-4">
    <h2>Modifier l'utilisateur</h2>
    <form method="POST" action="index.php?action=admin&subaction=editUser">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
        
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label>Rôle</label>
            <select class="form-control" name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Modérateur</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        <a href="index.php?action=admin" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once 'views/includes/footer.php'; ?>