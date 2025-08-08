<div class="wte-status-bar">
    <div class="wte-s-steps">
        <?php foreach ($self->getSteps()->asArray() as $step): ?>
            <div class="wte-s-step --wte-<?php print isset($step->state)? $step->state : ''; ?>">
                <div class="wte-status-bar-line"> </div>
                <div class="wte-percentage"><?php print $step->compoundPercentage; ?>%</div>
                <div class="wte-s-step-icon"><?php print $this->getIconMarkup() ?></div>
                <div class="wte-s-step-label"><?php print isset($step->label)? $step->label : ''; ?></div>
                <div class="wte-s-step-description"><?php print isset($step->description)? $step->description : ''; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>