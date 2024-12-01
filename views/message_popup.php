<?php if (!empty($message)): ?>
    <div id="message-popup" class="message-popup <?php echo htmlspecialchars($message_type); ?>">
        <div class="message-content">
            <span class="message-text"><?php echo htmlspecialchars($message); ?></span>
            <span class="close-button" onclick="closeMessagePopup()">&times;</span>
        </div>
        <div id="progress-bar" class="progress-bar"></div>
    </div>
<?php endif; ?>
