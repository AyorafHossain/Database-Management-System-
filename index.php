<?php


$conn = new mysqli("localhost", "root", "", "hospital_system");


if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);


$message = $search_query = $search_by = $display_by = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_patient"])) 
{
    $sql = "INSERT INTO patients (patient_id, patient_name, age, disease, doctor) VALUES ('{$_POST["patient_id"]}', '{$_POST["patient_name"]}', '{$_POST["age"]}', '{$_POST["disease"]}', '{$_POST["doctor"]}')";
    $message = $conn->query($sql) ? "<p class='success'>✓ Patient added successfully!</p>" : "<p class='error'>✗ Error: " . $conn->error . "</p>";
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_patient"])) 
{
    $sql = "UPDATE patients SET patient_name = '{$_POST["patient_name"]}', age = '{$_POST["age"]}', disease = '{$_POST["disease"]}', doctor = '{$_POST["doctor"]}' WHERE patient_id = '{$_POST["patient_id"]}'";
    $message = $conn->query($sql) ? "<p class='success'>✓ Patient updated successfully!</p>" : "<p class='error'>✗ Error: " . $conn->error . "</p>";
}

if (isset($_GET["delete"]))
{
    $sql = "DELETE FROM patients WHERE patient_id = '{$_GET["delete"]}'";
    $message = $conn->query($sql) ? "<p class='success'>✓ Patient deleted successfully!</p>" : "<p class='error'>✗ Error: " . $conn->error . "</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"]))
 {
    $search_query = $_POST["search_input"];
    $search_by = $_POST["search_by"];
    $display_by = $_POST["display_by"];
    
    if ($search_by == "patient_id") {
        $result = $conn->query("SELECT * FROM patients WHERE patient_id LIKE '%$search_query%'");
    }
     elseif ($search_by == "patient_name") {
        $result = $conn->query("SELECT * FROM patients WHERE patient_name LIKE '%$search_query%'");
    }
     elseif ($search_by == "disease") {
        $result = $conn->query("SELECT * FROM patients WHERE disease LIKE '%$search_query%'");
    } 
    elseif ($search_by == "doctor") {
        $result = $conn->query("SELECT * FROM patients WHERE doctor LIKE '%$search_query%'");
    }
} 
else 
{
    $display_by = "all";
    $result = $conn->query("SELECT * FROM patients");
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>Hospital Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .header { background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .content { padding: 25px; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #667eea; }
        .form-section h2 { color: #1e3c72; font-size: 18px; margin-bottom: 15px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px; }
        .form-group { display: flex; flex-direction: column; }
        label { color: #2c3e50; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
        input, select { padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 13px; }
        input:focus, select:focus { outline: none; border-color: #667eea; }
        .button-group { display: flex; gap: 8px; margin-top: 15px; flex-wrap: wrap; }
        button { padding: 10px 18px; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-search { background: linear-gradient(135deg, #f093fb, #f5576c); color: white; }
        .btn-search:hover { transform: translateY(-2px); }
        .btn-clear { background: #95a5a6; color: white; }
        .btn-clear:hover { background: #7f8c8d; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #f5576c; }
        .records-header { color: #1e3c72; font-size: 18px; margin: 20px 0 15px 0; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th { background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 12px; text-align: left; font-weight: 600; font-size: 13px; }
        td { padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 13px; }
        tr:hover { background: #f8f9fa; }
        .action-btn { padding: 6px 10px; margin-right: 4px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-edit { background: #3498db; color: white; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-delete:hover { background: #c0392b; }
        .no-data { text-align: center; padding: 30px; color: #7f8c8d; }
        #editForm { margin-top: 20px; animation: slideIn 0.3s; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } .button-group { flex-direction: column; } button { width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hospital Management System</h1>
            <p>Manage Patient Records</p>
        </div>
        <div class="content">
            <?php echo $message; ?>
            
            <div class="form-section">
                <h2></h2>Add New Patient</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group"><label>Patient ID:</label><input type="text" name="patient_id" required></div>
                        <div class="form-group"><label>Patient Name:</label><input type="text" name="patient_name" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Age:</label><input type="number" name="age" required></div>
                        <div class="form-group"><label>Disease:</label><input type="text" name="disease" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Doctor:</label><input type="text" name="doctor" required></div>
                    </div>
                    <button type="submit" name="add_patient" class="btn-primary">Add Patient</button>
                </form>
            </div>

            <!-- Replaced simple search with advanced search using dropdowns -->
            <div class="form-section">
                <h2>Search</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Search By:</label>
                            <select name="search_by" required>
                                <option value="">-- Select --</option>
                                <option value="patient_id">Patient ID</option>
                                <option value="patient_name">Patient Name</option>
                                <option value="disease">Disease</option>
                                <option value="doctor">Doctor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Search Value:</label>
                            <input type="text" name="search_input" placeholder="Enter search value..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Display:</label>
                            <select name="display_by">
                                <option value="">-- Select --</option>
                                <option value="patient_id" <?php if($display_by == "patient_id") echo "selected"; ?>>Patient ID Only</option>
                                <option value="patient_name" <?php if($display_by == "patient_name") echo "selected"; ?>>Name Only</option>
                                <option value="disease" <?php if($display_by == "disease") echo "selected"; ?>>Disease Only</option>
                                <option value="doctor" <?php if($display_by == "doctor") echo "selected"; ?>>Doctor Only</option>
                                <option value="all" <?php if($display_by == "all") echo "selected"; ?>>All Information</option>
                            </select>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" name="search" class="btn-search">Search</button>
                        <a href="index.php" style="text-decoration: none;"><button type="button" class="btn-clear">Clear</button></a>
                    </div>
                </form>
            </div>
            
            <h2 class="records-header">Patient Records <?php if($search_query) echo "- <strong>$search_query</strong>"; ?></h2>
            <table>
                <tr>
                    <!-- Dynamically show/hide columns based on display_by selection -->
                    <?php if($display_by == "all" || $display_by == "") { ?>
                        <th>Patient ID</th><th>Name</th><th>Age</th><th>Disease</th><th>Doctor</th><th>Date</th><th>Actions</th>
                    <?php }
                     elseif($display_by == "patient_id") { ?>
                        <th>Patient ID</th><th>Actions</th>
                    <?php } 
                    elseif($display_by == "patient_name") { ?>
                        <th>Patient ID</th><th>Name</th><th>Actions</th>
                    <?php } 
                    elseif($display_by == "disease") { ?>
                        <th>Patient ID</th><th>Disease</th><th>Actions</th>
                    <?php } 
                    elseif($display_by == "doctor") { ?>
                        <th>Patient ID</th><th>Doctor</th><th>Actions</th>
                    <?php } ?>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        if($display_by == "all" || $display_by == "") {
                            echo "<tr>
                                <td>{$row["patient_id"]}</td>
                                <td>{$row["patient_name"]}</td>
                                <td>{$row["age"]}</td>
                                <td>{$row["disease"]}</td>
                                <td>{$row["doctor"]}</td>
                                <td>{$row["admission_date"]}</td>
                                <td>
                                    <button class='action-btn btn-edit' onclick='editPatient(\"{$row["patient_id"]}\", \"{$row["patient_name"]}\", {$row["age"]}, \"{$row["disease"]}\", \"{$row["doctor"]}\")'>Edit</button>
                                    <a href='?delete={$row["patient_id"]}' onclick='return confirm(\"Delete?\")' style='text-decoration: none;'><button class='action-btn btn-delete' type='button'>Delete</button></a>
                                </td>
                            </tr>";
                        }
                         elseif($display_by == "patient_id") {
                            echo "<tr>
                                <td>{$row["patient_id"]}</td>
                                <td>
                                    <button class='action-btn btn-edit' onclick='editPatient(\"{$row["patient_id"]}\", \"{$row["patient_name"]}\", {$row["age"]}, \"{$row["disease"]}\", \"{$row["doctor"]}\")'>Edit</button>
                                    <a href='?delete={$row["patient_id"]}' onclick='return confirm(\"Delete?\")' style='text-decoration: none;'><button class='action-btn btn-delete' type='button'>Delete</button></a>
                                </td>
                            </tr>";
                        } 
                        elseif($display_by == "patient_name") {
                            echo "<tr>
                                <td>{$row["patient_id"]}</td>
                                <td>{$row["patient_name"]}</td>
                                <td>
                                    <button class='action-btn btn-edit' onclick='editPatient(\"{$row["patient_id"]}\", \"{$row["patient_name"]}\", {$row["age"]}, \"{$row["disease"]}\", \"{$row["doctor"]}\")'>Edit</button>
                                    <a href='?delete={$row["patient_id"]}' onclick='return confirm(\"Delete?\")' style='text-decoration: none;'><button class='action-btn btn-delete' type='button'>Delete</button></a>
                                </td>
                            </tr>";
                        } 
                        elseif($display_by == "disease") {
                            echo "<tr>
                                <td>{$row["patient_id"]}</td>
                                <td>{$row["disease"]}</td>
                                <td>
                                    <button class='action-btn btn-edit' onclick='editPatient(\"{$row["patient_id"]}\", \"{$row["patient_name"]}\", {$row["age"]}, \"{$row["disease"]}\", \"{$row["doctor"]}\")'>Edit</button>
                                    <a href='?delete={$row["patient_id"]}' onclick='return confirm(\"Delete?\")' style='text-decoration: none;'><button class='action-btn btn-delete' type='button'>Delete</button></a>
                                </td>
                            </tr>";
                        }
                         elseif($display_by == "doctor") {
                            echo "<tr>
                                <td>{$row["patient_id"]}</td>
                                <td>{$row["doctor"]}</td>
                                <td>
                                    <button class='action-btn btn-edit' onclick='editPatient(\"{$row["patient_id"]}\", \"{$row["patient_name"]}\", {$row["age"]}, \"{$row["disease"]}\", \"{$row["doctor"]}\")'>Edit</button>
                                    <a href='?delete={$row["patient_id"]}' onclick='return confirm(\"Delete?\")' style='text-decoration: none;'><button class='action-btn btn-delete' type='button'>Delete</button></a>
                                </td>
                            </tr>";
                        }
                    }
                } 
                else {
                    echo "<tr><td colspan='7' class='no-data'>No patients found</td></tr>";
                }
                ?>
            </table>
            
            <div id="editForm" style="display:none;">
                <div class="form-section">
                    <h2>Update Patient</h2>
                    <form method="POST">
                        <input type="hidden" id="editPatientId" name="patient_id">
                        <div class="form-row">
                            <div class="form-group"><label>Patient Name:</label><input type="text" id="editName" name="patient_name" required></div>
                            <div class="form-group"><label>Age:</label><input type="number" id="editAge" name="age" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Disease:</label><input type="text" id="editDisease" name="disease" required></div>
                            <div class="form-group"><label>Doctor:</label><input type="text" id="editDoctor" name="doctor" required></div>
                        </div>
                        <div class="button-group">
                            <button type="submit" name="update_patient" class="btn-primary">Update</button>
                            <button type="button" class="btn-clear" onclick="document.getElementById('editForm').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function editPatient(patientId, name, age, disease, doctor) {
            document.getElementById("editPatientId").value = patientId;
            document.getElementById("editName").value = name;
            document.getElementById("editAge").value = age;
            document.getElementById("editDisease").value = disease;
            document.getElementById("editDoctor").value = doctor;
            document.getElementById("editForm").style.display = "block";
            document.getElementById("editForm").scrollIntoView();
        }
    </script>
</body>
</html>
