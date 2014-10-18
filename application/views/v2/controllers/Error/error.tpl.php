<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <head>
        <title><?php echo ucfirst($this->langs->debugger_title); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->getCharset(); ?>" />
    </head>
    <body>
        <header style="border:2px solid #ff0000; width:450px; margin:0px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000"><?php echo ucfirst($this->langs->debugger_header); ?> :</span>
            <p><b><?php echo ucfirst($this->error->type); ?></b> : <?php echo $this->error->message; ?></p>
        </header>
        <section style="border:2px solid #ff0000; width:450px; margin:3px auto;padding:5px;background-color:#ffffff">
            <span style="font-weight:bold;text-align:center;color:#ff0000"><?php echo ucfirst($this->langs->debugger_section); ?> :</span>
            <p><b><?php echo ucfirst($this->langs->debugger_type); ?></b> : <?php echo $this->error->type; ?></p>
            <p><b><?php echo ucfirst($this->langs->debugger_code); ?></b> : <?php echo $this->error->code; ?></p>
            <p><b><?php echo ucfirst($this->langs->debugger_file); ?></b> : <?php echo $this->error->file; ?></p>
            <p><b><?php echo ucfirst($this->langs->debugger_line); ?></b> : <?php echo $this->error->line; ?></p>
            <p><b><?php echo ucfirst($this->langs->debugger_message); ?></b> : <?php echo $this->error->message; ?></p>
        </section>
    </body>
</html>