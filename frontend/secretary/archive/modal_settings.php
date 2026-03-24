<div id="settingsModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    
    <div class="modal-content small-modal" style="background:white; padding:25px; border-radius:8px; width:350px; margin:10% auto; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
        
        <h3 style="margin-top:0; color:#2c3e50;">Archive Policy</h3>
        <p style="color:#666; font-size:13px; line-height:1.5;">
            Define the age threshold for automatically archiving old schedules.
        </p>
        
        <form id="settingsForm">
            
            <div style="margin:20px 0;">
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Archive schedules older than:</label>
                
                <select name="threshold_months" id="thresholdInput" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                    <option value="6">6 Months (1 Semester)</option>
                    <option value="12">12 Months (1 Year)</option>
                    <option value="24">24 Months (2 Years)</option>
                    <option value="36">36 Months (3 Years)</option>
                </select>
            </div>
            
            <div class="modal-actions" style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeSettings()" style="background:none; border:1px solid #ccc; padding:8px 15px; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#3498db; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">Save Policy</button>
            </div>

        </form>
    </div>
</div>