<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Custom API Messages
  |--------------------------------------------------------------------------
  */

  // Success Messages
  'success' => [
    'created' => 'تم الإنشاء بنجاح',
    'updated' => 'تم التحديث بنجاح',
    'deleted' => 'تم الحذف بنجاح',
    'saved' => 'تم الحفظ بنجاح',
    'submitted' => 'تم الإرسال بنجاح',
  ],

  // Error Messages
  'error' => [
    'not_found' => 'العنصر غير موجود',
    'unauthorized' => 'غير مصرح لك بهذا الإجراء',
    'forbidden' => 'الوصول محظور',
    'server_error' => 'حدث خطأ في السيرفر',
    'validation_failed' => 'فشل التحقق من البيانات',
  ],

  // Employee Messages
  'employee' => [
    'created' => 'تم إضافة الموظف بنجاح',
    'updated' => 'تم تحديث بيانات الموظف بنجاح',
    'deleted' => 'تم حذف الموظف بنجاح',
    'not_found' => 'الموظف غير موجود',
  ],

  // Attendance Messages
  'attendance' => [
    'clock_in' => 'تم تسجيل الدخول بنجاح',
    'clock_out' => 'تم تسجيل الخروج بنجاح',
    'already_clocked_in' => 'لقد قمت بتسجيل الدخول مسبقاً',
    'not_clocked_in' => 'لم تقم بتسجيل الدخول بعد',
  ],

  // Leave Messages
  'leave' => [
    'submitted' => 'تم تقديم طلب الإجازة بنجاح',
    'approved' => 'تم اعتماد طلب الإجازة',
    'rejected' => 'تم رفض طلب الإجازة',
    'cancelled' => 'تم إلغاء طلب الإجازة',
    'insufficient_balance' => 'رصيد الإجازات غير كافٍ',
  ],

  // Payroll Messages
  'payroll' => [
    'generated' => 'تم إنشاء كشف الراتب بنجاح',
    'processed' => 'تم معالجة الرواتب بنجاح',
    'approved' => 'تم اعتماد كشف الراتب',
  ],

  // Authentication Messages
  'auth' => [
    'login_success' => 'تم تسجيل الدخول بنجاح',
    'logout_success' => 'تم تسجيل الخروج بنجاح',
    'invalid_credentials' => 'بيانات الدخول غير صحيحة',
    'password_changed' => 'تم تغيير كلمة المرور بنجاح',
  ],

  // Recruitment Messages
  'recruitment' => [
    'job_posted' => 'تم نشر الوظيفة بنجاح',
    'application_submitted' => 'تم تقديم الطلب بنجاح',
    'candidate_added' => 'تم إضافة المرشح بنجاح',
    'stage_updated' => 'تم تحديث مرحلة المرشح',
  ],

  // Onboarding Messages
  'onboarding' => [
    'checklist_created' => 'تم إنشاء قائمة التأهيل بنجاح',
    'task_completed' => 'تم إكمال المهمة بنجاح',
    'progress_updated' => 'تم تحديث التقدم',
  ],

];
