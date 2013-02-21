<div class="alert alert-error">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <string>Error!</string>
        <?php if ($unknown_game): ?>
            Unknown game!
        <?php endif; ?>
        <?php if ($bad_rounds): ?>
            Error fetching game rounds!
        <?php endif; ?>
        <?php if ($inconsistent_points): ?>
            Game accumulated rounds points mismatch game total points!
        <?php endif; ?>
    </ul>
</div>

