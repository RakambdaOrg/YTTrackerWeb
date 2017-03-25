<form method="get">
    <label>
        Start:
        <input type="datetime-local" name="startPeriod"<?php
        if(isset($_GET['startPeriod'])){
            echo ' value="' . $_GET['startPeriod'] . '"';
        }
        ?>>
    </label>
    <label>
        End:
        <input type="datetime-local" name="endPeriod"<?php
        if(isset($_GET['startPeriod'])){
            echo ' value="' . $_GET['endPeriod'] . '"';
        }
        ?>>
    </label>
    <label>
        <input type="submit" id="submitPeriod" value="Submit"/>
    </label>
</form>