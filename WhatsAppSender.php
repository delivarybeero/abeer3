<?php
class WhatsAppSender {
    private $api_url = 'https://api.whatsapp.com/send';
    
    public function sendInvoice($phone, $message) {
        // تنظيف رقم الهاتف وإضافة مفتاح الدولة إذا لزم الأمر
        $clean_phone = $this->cleanPhoneNumber($phone);
        
        // ترميز الرسالة للرابط
        $encoded_message = urlencode($message);
        
        // إنشاء رابط واتساب
        $whatsapp_url = "{$this->api_url}?phone={$clean_phone}&text={$encoded_message}";
        
        // في بيئة إنتاج حقيقية، سنستخدم cURL لإرسال الرسالة
        // لكن للتبسيط سنقوم بإعادة توجيه المستخدم أو حفظ الرابط
        
        // يمكنك استخدام هذا الرابط لفتح واتساب في نافذة جديدة
        // أو استخدام واجهة برمجة تطبيقات واتساب للأعمال إذا كان لديك حساب
        return $whatsapp_url;
    }
    
    private function cleanPhoneNumber($phone) {
        // إزالة أي أحرف غير رقمية
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // إذا كان الرقم يبدأ بـ 0، نستبدلها بـ 218 (مثال لليبيا)
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '218' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }
}
?>