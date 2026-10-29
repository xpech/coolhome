<?= $this->extend('templates/default') ?>
<?= $this->section('title') ?>Partager la maison<?= $this->endSection() ?>
<?= $this->section('breadcrumb') ?><a href="/home/index/<?= (int) $home['id'] ?>" class="ch-backlink">Retour a la maison</a><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="mx-auto max-w-[980px]">
    <h1 class="text-2xl font-bold mb-4">Partager <?= esc($home['title']) ?></h1>
    <?php if ($error): ?><p role="alert" class="ch-card mb-4 text-red-700"><?= esc($error) ?></p><?php endif; ?>
    <?php if ($message): ?><p role="status" class="ch-card mb-4 text-green-700"><?= esc($message) ?></p><?php endif; ?>
    <?php if ($invitationUrl): ?>
        <div class="ch-card mb-4">
            <label class="ch-label" for="invitation-url">Lien d'invitation a copier / coller</label>
            <input id="invitation-url" class="ch-input mb-3" type="text" readonly value="<?= esc($invitationUrl, 'attr') ?>" onclick="this.select();">
            <button type="button" class="ch-btn ch-btn-neutral" id="copy-invitation">Copier le lien</button>
            <p id="copy-result" role="status" class="mt-2 text-sm text-gray-500">Valable 7 jours pour l'email invite. Un nouvel envoi invalide le lien precedent.</p>
        </div>
    <?php endif; ?>
    <form method="post" class="ch-card mb-4">
        <?= csrf_field() ?>
        <h2 class="text-xl font-bold mb-3">Inviter une personne</h2>
        <p class="mb-3">Owner : tous les droits, y compris partager et supprimer. Habitant : modes, temperatures et programmation.</p>
        <label class="ch-label" for="email">Email</label>
        <input class="ch-input mb-3" id="email" type="email" name="email" maxlength="254" required>
        <label class="ch-label" for="role">Role</label>
        <select class="ch-input mb-3" id="role" name="role">
            <option value="resident">Habitant</option>
            <option value="owner">Owner</option>
        </select>
        <p class="mb-3 text-sm text-gray-500">Lien valable 7 jours, affiche apres creation pour etre copie. Envoi par email si le SMTP est configure. La personne pourra creer son compte depuis l'invitation. Pour generer un nouveau lien, saisissez a nouveau son email.</p>
        <button class="ch-btn ch-btn-primary" name="action" value="Invite">Creer l'invitation</button>
    </form>

    <div class="ch-card mb-4">
        <h2 class="text-xl font-bold mb-3">Membres</h2>
        <p class="mb-3"><?= esc($owner['email'] ?? '') ?> — Owner initial (acces permanent)</p>
        <?php foreach ($members as $member): ?>
            <form method="post" class="flex flex-wrap items-center gap-2 mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int) $member['user'] ?>">
                <span><?= esc($member['email']) ?></span>
                <select class="ch-input" name="role" aria-label="Role de <?= esc($member['email'], 'attr') ?>">
                    <option value="resident" <?= $member['role'] === 'resident' ? 'selected' : '' ?>>Habitant</option>
                    <option value="owner" <?= $member['role'] === 'owner' ? 'selected' : '' ?>>Owner</option>
                </select>
                <button class="ch-btn ch-btn-primary" name="action" value="UpdateMember">Enregistrer</button>
                <button class="ch-btn ch-btn-danger" name="action" value="RemoveMember" onclick="return confirm('Retirer cet acces ?');">Retirer</button>
            </form>
        <?php endforeach; ?>
    </div>

    <div class="ch-card">
        <h2 class="text-xl font-bold mb-3">Invitations en attente</h2>
        <?php if ($invitations === []): ?><p>Aucune invitation en attente.</p><?php endif; ?>
        <?php foreach ($invitations as $invitation): ?>
            <form method="post" class="flex flex-wrap items-center gap-2 mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="invitation_id" value="<?= (int) $invitation['id'] ?>">
                <span><?= esc($invitation['email']) ?> — <?= esc(\App\Libraries\HomeAccess::label($invitation['role'])) ?>
                    (<?= $invitation['expires_at'] <= date('Y-m-d H:i:s') ? 'expiree' : 'expire le ' . esc($invitation['expires_at']) ?>)</span>
                <button class="ch-btn ch-btn-danger" name="action" value="CancelInvitation">Annuler</button>
            </form>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('copy-invitation')?.addEventListener('click', async () => {
    const input = document.getElementById('invitation-url');
    const result = document.getElementById('copy-result');
    input.select();
    try {
        await navigator.clipboard.writeText(input.value);
        result.textContent = 'Lien copie.';
    } catch (error) {
        result.textContent = 'Copie automatique indisponible. Le lien est selectionne : utilisez Ctrl+C ou Cmd+C.';
    }
});
</script>
<?= $this->endSection() ?>
