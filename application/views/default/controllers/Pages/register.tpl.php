<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <?php include $this->getPath() . 'includes' . DS . 'head.tpl.php'; ?>
    <body>
        <div class="content">
            <?php include $this->getPath() . 'includes' . DS . 'header.tpl.php'; ?>
            <article class="introduction">
                <h2><?php echo $this->page->langTitle->{$this->lang}; ?></h2>
                <?php echo $this->page->langContent->{$this->lang}; ?>
                <?php if (!$this->isConnected) { ?>
                    <?php include $this->getPath() . 'forms' . DS . 'register.tpl.php'; ?>
                <?php } ?>
            </article>
            <?php include $this->getPath() . 'includes' . DS . 'footer.tpl.php'; ?>
        </div>
    </body>
</html>