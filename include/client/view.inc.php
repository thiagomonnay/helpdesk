<?php
if(!defined('OSTCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkClientAccess($thisclient)) die('Access Denied!');

$info=($_POST && $errors)?Format::htmlchars($_POST):array();

$dept = $ticket->getDept();
//Making sure we don't leak out internal dept names
if(!$dept || !$dept->isPublic())
    $dept = $cfg->getDefaultDept();

?>
<table width="800" cellpadding="1" cellspacing="0" border="0" id="ticketInfo">
    <tr>
        <td colspan="2" width="100%">
            <h1>
                Ticket #<?php echo $ticket->getExtId(); ?> &nbsp;
                <a href="view.php?id=<?php echo $ticket->getExtId(); ?>" title="Reload"><span class="Icon refresh">&nbsp;</span></a>
            </h1>
        </td>
    </tr> 
    <tr>
        <td width="50%">   
            <table class="infoTable" cellspacing="1" cellpadding="3" width="100%" border="0">
                <tr>
                    <th width="100">Status do Ticket:</th>
                    <td><?php echo ucfirst($ticket->getStatus()); ?></td>
                </tr>
                <tr>
                    <th>Departamento:</th>
                    <td><?php echo Format::htmlchars($dept->getName()); ?></td>
                </tr>
                <tr>
                    <th>Data de Criação:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
           </table>
       </td>
       <td width="50%">
           <table class="infoTable" cellspacing="1" cellpadding="3" width="100%" border="0">
               <tr>
                   <th width="100">Nome:</th>
                   <td><?php echo ucfirst($ticket->getName()); ?></td>
               </tr>
               <tr>
                   <th width="100">Email:</th>
                   <td><?php echo Format::htmlchars($ticket->getEmail()); ?></td>
               </tr>
               <tr>
                   <th>Fone:</th>
                   <td><?php echo $ticket->getPhoneNumber(); ?></td>
               </tr>
            </table>
       </td>
    </tr>
</table>
<br>
<h2>Assunto:<?php echo Format::htmlchars($ticket->getSubject()); ?></h2>
<br>
<span class="Icon thread">Tópico do ticket</span>
<div id="ticketThread">
<?php    
if($ticket->getThreadCount() && ($thread=$ticket->getClientThread())) {
    $threadType=array('M' => 'message', 'R' => 'response');
    foreach($thread as $entry) {
        //Making sure internal notes are not displayed due to backend MISTAKES!
        if(!$threadType[$entry['thread_type']]) continue;
        $poster = $entry['poster'];
        if($entry['thread_type']=='R' && ($cfg->hideStaffName() || !$entry['staff_id']))
            $poster = ' ';
        ?>
        <table class="<?php echo $threadType[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="1" width="800" border="0">
            <tr><th><?php echo Format::db_datetime($entry['created']); ?> &nbsp;&nbsp;<span><?php echo $poster; ?></span></th></tr>
            <tr><td><?php echo Format::display($entry['body']); ?></td></tr>
            <?php
            if($entry['attachments']
                    && ($tentry=$ticket->getThreadEntry($entry['id']))
                    && ($links=$tentry->getAttachmentsLinks())) { ?>
                <tr><td class="info"><?php echo $links; ?></td></tr>
            <?php
            } ?>
        </table>
    <?php
    }
}
?>
</div>
<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>
<form id="reply" action="tickets.php?id=<?php echo $ticket->getExtId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
    <?php csrf_token(); ?>
    <h2>Responder</h2>
    <input type="hidden" name="id" value="<?php echo $ticket->getExtId(); ?>">
    <input type="hidden" name="a" value="reply">
    <table border="0" cellspacing="0" cellpadding="3" width="800">
        <tr>
            <td width="160">
                <label>Mensagem:</label>
            </td>
            <td width="640">
                <?php
                if($ticket->isClosed()) {
                    $msg='<b>Ticket será reaberto  após mensagem</b>';
                } else {
                    $msg='Para melhor lhe ajudar, por favor, seja específico e detalhado';
                }
                ?>
                <span id="msg"><em><?php echo $msg; ?> </em></span><font class="error">*&nbsp;<?php echo $errors['message']; ?></font><br/>
                <textarea name="message" id="message" cols="50" rows="9" wrap="soft"><?php echo $info['message']; ?></textarea>
            </td>
        </tr>
        <?php
        if($cfg->allowOnlineAttachments()) { ?>
        <tr>
            <td width="160">
                <label for="attachment">Anexos:</label>
            </td>
            <td width="640" id="reply_form_attachments" class="attachments">
                <div class="uploads">
                </div>
                <div class="file_input">
                    <input class="multifile" type="file" name="attachments[]" size="30" value="" />
                </div>
            </td>
        </tr>
        <?php
        } ?>
    </table>
    <p style="padding-left:165px;">
        <input type="submit" value="Enviar">
        <input type="reset" value="Limpar">
        <input type="button" value="Cancelar" onClick="history.go(-1)">
    </p>
</form>
