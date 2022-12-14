<#1>
<?php
/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists("mcc_course_settings")) {
    $ilDB->createTable("mcc_course_settings", [
        'course_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        "chat_integration_enabled" => [
            "type" => "integer",
            "length" => 1,
            'notnull' => true,
            "default" => 0,
        ],
        "matrix_room_id" => [
            "type" => "text",
            "length" => 92,
            "notnull" => false,
        ]
    ]);
    $ilDB->addPrimaryKey("mcc_course_settings", ["course_id"]);
}
?>
<#2>
<?php
if (!$ilDB->tableExists("mcc_user_device")) {
    $ilDB->createTable("mcc_user_device", [
        'user_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
        ],
        "device_id" => [
            "type" => "text",
            "length" => 96,
            "notnull" => true,
        ],
    ]);
    $ilDB->addPrimaryKey("mcc_user_device", ["user_id"]);
}
?>
