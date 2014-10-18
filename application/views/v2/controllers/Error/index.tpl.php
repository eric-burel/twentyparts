<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <?php include $this->getPath() . 'includes' . DS . 'head.tpl.php'; ?>
    <body>

        <?php include $this->getPath() . 'includes' . DS . 'header.tpl.php'; ?>
        <section>
            <h1 class="align-center margin-top-60"><?php echo $this->errorInfo['code'] . ' ' . $this->errorInfo['message']; ?></h1>
        </section>
        <?php include $this->getPath() . 'includes' . DS . 'footer.tpl.php'; ?>
    </body>
</html>