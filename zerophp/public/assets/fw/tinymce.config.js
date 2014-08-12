tinymce.init({
	mode : "exact",
    elements : "rte,rte1,rte2,rte3,rte4",
    theme: "modern",
    language : 'vi',
    relative_urls: false,
    width: 680,
    height: 300,
    plugins: [
         "advlist autolink link image lists charmap hr anchor",
         "searchreplace wordcount visualblocks visualchars",
         "table contextmenu directionality emoticons paste textcolor code"
   ],
   toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent ",
   toolbar2: "| link unlink anchor | image emoticons charmap | forecolor backcolor | styleselect | visualblocks | code", 
   image_advtab: true ,
   
   external_filemanager_path:"/assets/filemanager/",
   filemanager_title:"Quản lý ảnh" ,
   external_plugins: { "filemanager" : "/assets/filemanager/plugin.min.js"}
 });