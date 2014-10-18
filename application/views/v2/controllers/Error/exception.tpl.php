<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <head>
        <title><?php echo ucfirst($this->langs->debugger_title); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->getCharset(); ?>" />
    </head>
    <body>
        <header style="border:2px solid #ff0000; width:450px; margin:0px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000"><?php echo ucfirst($this->langs->debugger_header); ?>:</span>
            <p><b><?php echo ucfirst($this->langs->e_exception); ?></b> : <?php echo $this->exception->message; ?></p>
        </header>
        <section style="border:2px solid #ff0000; width:450px; margin:3px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000"><?php echo ucfirst($this->langs->debugger_section); ?> :</span>
            <p><b><?php echo ucfirst($this->langs->debugger_file); ?></b> : <?php echo $this->exception->file; ?></p>
            <p><b><?php echo ucfirst($this->langs->debugger_line); ?></b> : <?php echo $this->exception->line; ?></p>
            <p><b><?php echo ucfirst($this->langs->debugger_trace); ?></b> :<br/><?php echo nl2br($this->exception->trace); ?></p>
        </section>
    </body>
</html>