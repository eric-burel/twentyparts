<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <?php include $this->getPath() . 'includes' . DS . 'head.tpl.php'; ?>
    <body>
        <?php include $this->getPath() . 'includes' . DS . 'header.tpl.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-offset-1 col-lg-7">
                    <h2><?php echo $this->page->langTitle->{$this->lang}; ?></h2>
                    <?php echo $this->page->langContent->{$this->lang}; ?>
                    <?php include $this->getPath() . 'blocks' . DS . 'lastNews.tpl.php'; ?>
                </div>

                <div class="col-lg-4">
                    <?php if (!$this->isConnected) { ?>
                        <?php include $this->getPath() . 'forms' . DS . 'register.tpl.php'; ?>
                    <?php } ?>
                    <?php include $this->getPath() . 'blocks' . DS . 'lastStory.tpl.php'; ?>
                </div>
            </div>
        </div>
        <?php include $this->getPath() . 'includes' . DS . 'footer.tpl.php'; ?>
    </body>
</html>