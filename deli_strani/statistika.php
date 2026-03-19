
<div class="add-habit-form" id="statistikaModal">
    <div class="form-header">
        <div class="form-header-title">Statistika</div>
        <button type="button" class="close-btn" id="closeStatistika">&times;</button>
    </div>
    <div class="form-content">
        <div class="stat-cards-grid">

            <div class="stat-card-item">
                <div class="stat-card-num"><?php echo $statOpravljenih ?? 0; ?></div>
                <div class="stat-card-label">Skupno opravljenih</div>
            </div>

            <div class="stat-card-item">
                <div class="stat-card-num"><?php echo $statMaxStreak ?? 0; ?></div>
                <div class="stat-card-label">Najdaljši streak <br>dni</div>
            </div>

            <div class="stat-card-item">
                <div class="stat-card-num"><?php echo $statNavad ?? 0; ?></div>
                <div class="stat-card-label">Aktivnih navad</div>
            </div>

        </div>
    </div>
</div>
