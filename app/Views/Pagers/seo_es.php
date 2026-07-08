<?php $pager->setSurroundCount(2) ?>

<ul class="pagination">
    <?php if ($pager->hasPreviousPage()) : ?>
        <li>
            <a href="<?= $pager->getFirst() ?>" aria-label="Primero">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <li>
            <a href="<?= $pager->getPreviousPage() ?>" aria-label="Anterior">
                <span aria-hidden="true">Ant</span>
            </a>
        </li>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <li <?= $link['active'] ? 'class="active"' : '' ?>>
            <a href="<?= $link['uri'] ?>">
                <?= $link['title'] ?>
            </a>
        </li>
    <?php endforeach ?>

    <?php if ($pager->hasNextPage()) : ?>
        <li>
            <a href="<?= $pager->getNextPage() ?>" aria-label="Siguiente">
                <span aria-hidden="true">Sig</span>
            </a>
        </li>
        <li>
            <a href="<?= $pager->getLast() ?>" aria-label="Último">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    <?php endif ?>
</ul>
