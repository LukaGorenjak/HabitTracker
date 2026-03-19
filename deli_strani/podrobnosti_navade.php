<div class="detail-panel" id="detailPanel">

    <div class="detail-empty" id="detailEmpty">
        Izberite navado za prikaz podrobnosti.
    </div>

    <div class="detail-content" id="detailContent">

        <div class="detail-header">
            <div class="detail-category-dot" id="detailCategoryDot"></div>
            <div class="detail-title" id="detailTitle"></div>
        </div>

        <div class="detail-streak-box">
            <span class="detail-streak-label">Trenutni streak</span>
            <span class="detail-streak-number" id="detailStreak">0</span>
            <span class="detail-streak-unit">dni</span>
        </div>

        <div class="detail-progress-section" id="detailProgressSection" style="display:none;">
            <div class="progress-chart-wrap">
                <canvas id="progressChart" width="140" height="140"></canvas>
                <div class="progress-chart-label" id="progressChartLabel"></div>
            </div>
            <div class="progress-goal-text" id="progressGoalText"></div>
        </div>

        <div class="detail-info">
            <div class="detail-row">
                <span class="detail-label">Kategorija</span>
                <span class="detail-value" id="detailKategorija"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Ponavljanje</span>
                <span class="detail-value" id="detailPonavljanje"></span>
            </div>
            <div class="detail-row" id="detailDneviRow">
                <span class="detail-label">Dnevi</span>
                <span class="detail-value" id="detailDnevi"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cilj</span>
                <span class="detail-value" id="detailCilj"></span>
            </div>
<div class="detail-row" id="detailDelDnevaRow">
                <span class="detail-label">Del dneva</span>
                <span class="detail-value" id="detailDelDneva"></span>
            </div>
            <div class="detail-row" id="detailOpisRow">
                <span class="detail-label">Opis</span>
                <span class="detail-value" id="detailOpis"></span>
            </div>
        </div>

        <?php include 'deli_strani/graf_navade.php'; ?>

        <div class="detail-actions">
            <button class="detail-btn detail-btn-edit" id="editHabitBtn">✏️ Uredi</button>
            <button class="detail-btn detail-btn-delete" id="deleteHabitBtn">🗑️ Izbriši</button>
        </div>

    </div>

</div>
