<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation" class="pagination-container">
    <ul class="pagination">
        <?php if ($pager->hasPrevious()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="true">««</span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="true">«</span>
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

        <?php if ($pager->hasNext()) : ?>
            <li>
                <a href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="true">»</span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="true">»»</span>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>

<style>
    .pagination-container {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 8px;
        align-items: center;
    }
    .pagination li a {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border-radius: 10px;
        background: white;
        border: 1px solid #e2e8f0;
        color: #64748b;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .pagination li a:hover {
        border-color: #2152ff;
        color: #2152ff;
        background: #f8faff;
        transform: translateY(-2px);
    }
    .pagination li.active a {
        background: #2152ff;
        color: white;
        border-color: #2152ff;
        box-shadow: 0 4px 12px rgba(33, 82, 255, 0.25);
    }
</style>
