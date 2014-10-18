<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <?php include $this->getPath() . 'includes' . DS . 'head.tpl.php'; ?>
    <body>
        <div class="content">
            <?php include $this->getPath() . 'includes' . DS . 'header.tpl.php'; ?>
            <article class="introduction">
                <h2><?php echo $this->new->langTitle->{$this->lang}; ?></h2>
                <?php echo $this->new->date; ?><br>
                <?php echo $this->new->langContent->{$this->lang}; ?>
            </article>
            <?php include $this->getPath() . 'includes' . DS . 'footer.tpl.php'; ?>
        </div>
    </body>
</html>