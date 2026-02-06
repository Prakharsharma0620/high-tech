<?php
session_start();
require 'db_connect.php';
require 'blob_storage.php';

// Initialize variables to avoid "Undefined Variable" notices in HTML
$post_name = $candidate_name_hindi = $candidate_name_english = $father_name = "";
$perm_tehsil = $perm_district = $perm_pincode = "";
$corr_tehsil = $corr_district = $corr_pincode = "";
$mobile_1 = $mobile_2_whatsapp = $aadhar_no = $old_epf_no = $old_esi_no = "";
$ifsc_code = $bank_name = $account_no = "";
$dob_day = $dob_month = $dob_year = "";
$gender = $category = $marital_status = "";
$nominee_name = $nominee_relation_age = "";
$exp_post_name = $exp_dept_name = $exp_from = $exp_to = "";
$decl_name = $decl_parent_name = $place = "";
$candidate_photo_url = "";
$date = date('Y-m-d');

// --- FORM SUBMISSION LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // 1. Upload Image to Vercel (if selected)
        if (isset($_FILES['candidate_photo']) && $_FILES['candidate_photo']['size'] > 0) {
            $candidate_photo_url = uploadToVercel($_FILES['candidate_photo']);
        }

        // 2. Prepare Data for MongoDB (Nested Document Structure)
        $document = [
            'registration_no' => 'REG-' . mt_rand(100000, 999999),
            'submitted_at' => new MongoDB\BSON\UTCDateTime(),
            'photo_url' => $candidate_photo_url, 
            
            'job_details' => ['post_name' => $_POST['post_name'] ?? ''],
            
            'personal_details' => [
                'name_hindi' => $_POST['candidate_name_hindi'] ?? '',
                'name_english' => $_POST['candidate_name_english'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'dob' => [
                    'day' => $_POST['dob_day'] ?? '',
                    'month' => $_POST['dob_month'] ?? '',
                    'year' => $_POST['dob_year'] ?? ''
                ],
                'gender' => $_POST['gender'] ?? '',
                'category' => $_POST['category'] ?? '',
                'marital_status' => $_POST['marital_status'] ?? ''
            ],
            
            'contact' => [
                'mobile' => $_POST['mobile_1'] ?? '',
                'whatsapp' => $_POST['mobile_2'] ?? ''
            ],
            
            'addresses' => [
                'permanent' => [
                    'tehsil' => $_POST['perm_tehsil'] ?? '',
                    'district' => $_POST['perm_district'] ?? '',
                    'pincode' => $_POST['perm_pincode'] ?? ''
                ],
                'correspondence' => [
                    'tehsil' => $_POST['corr_tehsil'] ?? '',
                    'district' => $_POST['corr_district'] ?? '',
                    'pincode' => $_POST['corr_pincode'] ?? ''
                ]
            ],
            
            'ids' => [
                'aadhar' => $_POST['aadhar_no'] ?? '',
                'epf' => $_POST['old_epf'] ?? '',
                'esi' => $_POST['old_esi'] ?? ''
            ],
            
            'bank' => [
                'ifsc' => $_POST['ifsc_code'] ?? '',
                'bank_name' => $_POST['bank_name'] ?? '',
                'account_no' => $_POST['account_no'] ?? ''
            ],
            
            'nominee' => [
                'name' => $_POST['nominee_name'] ?? '',
                'relation' => $_POST['nominee_relation'] ?? ''
            ],
            
            'education' => [
                'highschool' => [
                    'board' => $_POST['edu_hs_board'] ?? '', 'marks' => $_POST['edu_hs_marks'] ?? '', 'perc' => $_POST['edu_hs_perc'] ?? ''
                ],
                'intermediate' => [
                    'board' => $_POST['edu_int_board'] ?? '', 'marks' => $_POST['edu_int_marks'] ?? '', 'perc' => $_POST['edu_int_perc'] ?? ''
                ],
                'computer' => [
                    'board' => $_POST['edu_comp_board'] ?? '', 'marks' => $_POST['edu_comp_marks'] ?? '', 'perc' => $_POST['edu_comp_perc'] ?? ''
                ],
                'other' => [
                    'board' => $_POST['edu_oth_board'] ?? '', 'marks' => $_POST['edu_oth_marks'] ?? '', 'perc' => $_POST['edu_oth_perc'] ?? ''
                ]
            ],
            
            'experience' => [
                'post' => $_POST['exp_post'] ?? '',
                'dept' => $_POST['exp_dept'] ?? '',
                'from' => $_POST['exp_from'] ?? '',
                'to' => $_POST['exp_to'] ?? ''
            ],
            
            // Checkboxes (True/False)
            'checklists' => [
                'hs_cert' => isset($_POST['chk_hs']),
                'inter_cert' => isset($_POST['chk_int']),
                'aadhar_card' => isset($_POST['chk_aadhar']),
                'experience_cert' => isset($_POST['chk_exp']),
                'computer_cert' => isset($_POST['chk_comp']),
                'bank_passbook' => isset($_POST['chk_bank']),
                'pan_card' => isset($_POST['chk_pan'])
            ],
            
            'declaration' => [
                'signed_by' => $_POST['decl_name'] ?? '',
                'parent' => $_POST['decl_parent_name'] ?? '',
                'place' => $_POST['place'] ?? '',
                'date' => $_POST['date'] ?? ''
            ]
        ];

        // 3. Insert into MongoDB
        $collection = getDBConnection();
        $result = $collection->insertOne($document);

        if ($result->getInsertedCount() == 1) {
            echo "<script>alert('Application Submitted Successfully! Registration ID: " . $document['registration_no'] . "');</script>";
            // Optional: Redirect to prevent resubmission
            // header("Location: index.php"); exit;
        }

    } catch (Exception $e) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:15px;margin:20px;border:1px solid #f5c6cb;'>
              <strong>Error:</strong> " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form / आवेदन पत्र</title>
    <style>
        :root { --border-color: #333; }
        body { font-family: 'Segoe UI', 'Noto Sans Devanagari', sans-serif; background-color: #eef; margin: 0; padding: 20px; }
        .form-container { max-width: 950px; background: white; margin: 0 auto; padding: 40px; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { font-size: 20px; text-decoration: underline; text-transform: uppercase; text-align: center; }
        .header-section { margin-bottom: 20px; text-align: center; font-weight: bold; font-size: 14px; }
        .row { display: flex; flex-wrap: wrap; margin-bottom: 15px; align-items: flex-end; }
        .col { flex: 1; padding: 5px; }
        .col-half { flex: 0 0 48%; }
        label { font-weight: bold; display: block; margin-bottom: 5px; font-size: 13px; }
        input[type="text"], input[type="number"], input[type="date"], select { padding: 8px; border: 1px solid #aaa; width: 100%; box-sizing: border-box; border-bottom: 1px solid #000; background: #fafafa; }
        
        /* Photo Section */
        .photo-container { display: flex; justify-content: space-between; }
        .details-left { width: 78%; }
        .photo-box { width: 160px; height: 190px; border: 2px solid black; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; font-size: 11px; margin-top: 10px; background-color: #f8f8f8; padding: 5px; overflow: hidden; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .photo-input-overlay input { width: 100%; font-size: 10px; margin-top: 5px; }

        .address-group { border: 1px solid #ddd; padding: 15px; margin: 15px 0; background: #fdfdfd; position: relative; }
        .group-title { position: absolute; top: -12px; left: 10px; background: white; padding: 0 5px; font-weight: bold; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }

        .checklist { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 15px 0; }
        .checklist-item { display: flex; justify-content: space-between; align-items: center; font-size: 13px; padding-right: 10px; }
        .declaration { border-top: 2px solid #333; margin-top: 20px; padding-top: 20px; font-size: 14px; line-height: 1.6; text-align: justify; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        
        button.submit-btn {
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 12px 25px; 
            font-size: 16px; 
            cursor: pointer;
            border-radius: 4px;
        }
        button.submit-btn:hover { background: #218838; }

        @media print { body { background: white; padding: 0; } .form-container { box-shadow: none; border: none; padding: 0; } .photo-input-overlay, .submit-btn { display: none; } }
    </style>
</head>
<body>

<form method="POST" enctype="multipart/form-data">
<div class="form-container">
    <h1>Application Form Format / आवेदन पत्र का प्रारूप</h1>
    <div class="header-section">
        (Application for outsourcing posts under Municipal Corporation, Aligarh as personnel of Service Provider Agency Hi-Tech Consultancy Services)<br>
        (सेवा प्रदाता एजेन्सी हाई टेक कन्सलटेन्सी सर्विसेस के कर्मी के रूप में नगर निगम, अलीगढ़ के अन्तर्गत आउटसोर्सिंग के पदों पर कार्य करने हेतु आवेदन।)
    </div>

    <div class="photo-container">
        <div class="details-left">
            <div class="row"><div class="col"><label>1. Name of Applied Post / आवेदित पद का नाम:</label><input type="text" name="post_name" value="<?php echo $post_name; ?>"></div></div>
            <div class="row"><div class="col"><label>2. Candidate's Name (Hindi) / अभ्यर्थी का नाम (हिन्दी में):</label><input type="text" name="candidate_name_hindi" value="<?php echo $candidate_name_hindi; ?>"></div></div>
            <div class="row"><div class="col"><label>(In English Capital Letters) / (अंग्रेजी में कैपिटल लेटर में):</label><input type="text" name="candidate_name_english" style="text-transform: uppercase;" value="<?php echo $candidate_name_english; ?>"></div></div>
            <div class="row"><div class="col"><label>3. Father's Name / पिता का नाम:</label><input type="text" name="father_name" value="<?php echo $father_name; ?>"></div></div>
        </div>

        <div class="photo-box">
            <?php if (!empty($candidate_photo_url)): ?>
                <img src="<?php echo $candidate_photo_url; ?>" alt="Candidate Photo">
            <?php else: ?>
                <div>Paste Self-Attested Photo<br>(Upto Chest)<br><br>स्वप्रमाणित फोटो<br>वक्ष तक की फोटो<br>चस्पा करें।</div>
                <div class="photo-input-overlay"><input type="file" name="candidate_photo" accept="image/*"></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="address-group">
        <span class="group-title">4. (a) Permanent Address / (अ) स्थायी पता:</span>
        <div class="row">
            <div class="col"><label>Tehsil / तहसील:</label> <input type="text" name="perm_tehsil" value="<?php echo $perm_tehsil; ?>"></div>
            <div class="col"><label>District / जिला:</label> <input type="text" name="perm_district" value="<?php echo $perm_district; ?>"></div>
            <div class="col"><label>Pin / पिनकोड:</label> <input type="text" name="perm_pincode" value="<?php echo $perm_pincode; ?>"></div>
        </div>
    </div>

    <div class="address-group">
        <span class="group-title">4. (b) Correspondence Address / (ब) पत्र व्यवहार का पता:</span>
        <div class="row">
            <div class="col"><label>Tehsil / तहसील:</label> <input type="text" name="corr_tehsil" value="<?php echo $corr_tehsil; ?>"></div>
            <div class="col"><label>District / जिला:</label> <input type="text" name="corr_district" value="<?php echo $corr_district; ?>"></div>
            <div class="col"><label>Pin / पिनकोड:</label> <input type="text" name="corr_pincode" value="<?php echo $corr_pincode; ?>"></div>
        </div>
    </div>

    <div class="row">
        <div class="col"><label>5. Mobile 1 / मोबाईल 1:</label> <input type="text" name="mobile_1" value="<?php echo $mobile_1; ?>"></div>
        <div class="col"><label>WhatsApp / वाट्सअप:</label> <input type="text" name="mobile_2" value="<?php echo $mobile_2_whatsapp; ?>"></div>
    </div>

    <div class="row"><div class="col"><label>6. Aadhar No. / आधार नं0:</label> <input type="text" name="aadhar_no" value="<?php echo $aadhar_no; ?>"></div></div>

    <div class="row">
        <div class="col"><label>7. (a) Old EPF No.:</label> <input type="text" name="old_epf" value="<?php echo $old_epf_no; ?>"></div>
        <div class="col"><label>(b) Old ESI No.:</label> <input type="text" name="old_esi" value="<?php echo $old_esi_no; ?>"></div>
    </div>

    <div class="address-group">
        <span class="group-title">8. Bank Details / बैंक विवरण:</span>
        <div class="row">
            <div class="col"><label>IFSC Code:</label> <input type="text" name="ifsc_code" value="<?php echo $ifsc_code; ?>"></div>
            <div class="col"><label>Bank Name:</label> <input type="text" name="bank_name" value="<?php echo $bank_name; ?>"></div>
            <div class="col"><label>Account No:</label> <input type="text" name="account_no" value="<?php echo $account_no; ?>"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-half">
            <label>9. DOB (High School):</label>
            <div style="display:flex; gap:5px;">
                <input type="text" name="dob_day" placeholder="DD" value="<?php echo $dob_day; ?>">
                <input type="text" name="dob_month" placeholder="MM" value="<?php echo $dob_month; ?>">
                <input type="text" name="dob_year" placeholder="YYYY" value="<?php echo $dob_year; ?>">
            </div>
        </div>
        <div class="col-half">
            <div class="row"><div class="col"><label>10. Gender / लिंग:</label><select name="gender"><option>Male</option><option>Female</option></select></div></div>
            <div class="row"><div class="col"><label>11. Category / श्रेणी:</label><input type="text" name="category" value="<?php echo $category; ?>"></div></div>
        </div>
    </div>

    <div class="row"><div class="col"><label>12. Marital Status / वैवाहिक स्थिति:</label> <input type="text" name="marital_status" value="<?php echo $marital_status; ?>"></div></div>
    
    <div class="row">
        <div class="col"><label>13. Nominee Name:</label> <input type="text" name="nominee_name" value="<?php echo $nominee_name; ?>"></div>
        <div class="col"><label>Relation/Age:</label> <input type="text" name="nominee_relation" value="<?php echo $nominee_relation_age; ?>"></div>
    </div>

    <div class="row" style="margin-top:20px;"><label>14. Education / शैक्षिक योग्यता:</label></div>
    <table>
        <thead><tr><th>Exam</th><th>Board/Inst</th><th>Subj</th><th>Year</th><th>Marks</th><th>%</th></tr></thead>
        <tbody>
            <tr><td>High School</td><td><input type="text" name="edu_hs_board"></td><td><input type="text" name="edu_hs_subj"></td><td><input type="text" name="edu_hs_year"></td><td><input type="text" name="edu_hs_marks"></td><td><input type="text" name="edu_hs_perc"></td></tr>
            <tr><td>Inter/Diploma</td><td><input type="text" name="edu_int_board"></td><td><input type="text" name="edu_int_subj"></td><td><input type="text" name="edu_int_year"></td><td><input type="text" name="edu_int_marks"></td><td><input type="text" name="edu_int_perc"></td></tr>
            <tr><td>Computer</td><td><input type="text" name="edu_comp_board"></td><td><input type="text" name="edu_comp_subj"></td><td><input type="text" name="edu_comp_year"></td><td><input type="text" name="edu_comp_marks"></td><td><input type="text" name="edu_comp_perc"></td></tr>
            <tr><td>Other</td><td><input type="text" name="edu_oth_board"></td><td><input type="text" name="edu_oth_subj"></td><td><input type="text" name="edu_oth_year"></td><td><input type="text" name="edu_oth_marks"></td><td><input type="text" name="edu_oth_perc"></td></tr>
        </tbody>
    </table>

    <div class="row" style="margin-top:25px;"><label>15. Experience / अनुभव:</label></div>
    <div class="row">
        <div class="col"><label>Post:</label> <input type="text" name="exp_post" value="<?php echo $exp_post_name; ?>"></div>
        <div class="col"><label>Dept:</label> <input type="text" name="exp_dept" value="<?php echo $exp_dept_name; ?>"></div>
    </div>
    <div class="row">
        <div class="col"><label>From:</label> <input type="date" name="exp_from" value="<?php echo $exp_from; ?>"></div>
        <div class="col"><label>To:</label> <input type="date" name="exp_to" value="<?php echo $exp_to; ?>"></div>
    </div>

    <div class="row" style="margin-top: 25px;"><label>16. Checklists (Tick if attached):</label></div>
    <div class="checklist">
        <div class="checklist-item"><label>(1) High School Cert.</label> <input type="checkbox" name="chk_hs"></div>
        <div class="checklist-item"><label>(4) Experience Cert.</label> <input type="checkbox" name="chk_exp"></div>
        <div class="checklist-item"><label>(2) Inter Cert.</label> <input type="checkbox" name="chk_int"></div>
        <div class="checklist-item"><label>(5) Computer Cert.</label> <input type="checkbox" name="chk_comp"></div>
        <div class="checklist-item"><label>(3) Aadhar Card</label> <input type="checkbox" name="chk_aadhar"></div>
        <div class="checklist-item"><label>(6) Bank Passbook</label> <input type="checkbox" name="chk_bank"></div>
        <div class="checklist-item"><label>(7) PAN Card</label> <input type="checkbox" name="chk_pan"></div>
    </div>

    <div class="declaration">
        <h3>Oath / Declaration (शपथ / घोषणा-पत्र)</h3>
        <p>I declare that the information given is true. <br>(मैं घोषणा करता हूँ कि इस आवेदन पत्र में दी गई सूचनाएं सत्य है।)</p>
        <div class="row">
            <div class="col"><label>Name:</label> <input type="text" name="decl_name" value="<?php echo $decl_name; ?>"></div>
            <div class="col"><label>Parent Name:</label> <input type="text" name="decl_parent_name" value="<?php echo $decl_parent_name; ?>"></div>
        </div>
    </div>

    <div class="footer">
        <div>
            <div>Date: <input type="text" name="date" value="<?php echo $date; ?>" style="width: 150px; display:inline;"></div>
            <div>Place: <input type="text" name="place" value="<?php echo $place; ?>" style="width: 150px; display:inline;"></div>
        </div>
        <div style="text-align: center;">
            <div style="height: 40px;"></div>
            _____________________________ <br> Signature / हस्ताक्षर
            <br><br>
            <button type="submit" class="submit-btn">Submit Application</button>
        </div>
    </div>

</div>
</form>

</body>
</html>
