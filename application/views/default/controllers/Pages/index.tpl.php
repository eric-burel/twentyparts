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
                <!-- News -->
                <h2 class="ucfirst"><a href="<?php echo $this->getUrl('page', array('news')); ?>" ><?php echo $this->langs->frontoffice_23; ?></a></h2>
                <?php if (is_array($this->news) && count($this->news) > 0) { ?>
                    <?php foreach ($this->news as $new) { ?>
                        <?php echo $new->link; ?>
                        <p><strong><a class="black" href="<?php echo $this->getUrl('new', array($new->slug)); ?>" ><?php echo $new->date; ?></a> -</strong> <?php echo $new->langContent->{$this->lang}; ?></p>
                    <?php } ?>
                <?php } ?>
            </article>
            <?php include $this->getPath() . 'includes' . DS . 'footer.tpl.php'; ?>
        </div>
    </body>
</html>