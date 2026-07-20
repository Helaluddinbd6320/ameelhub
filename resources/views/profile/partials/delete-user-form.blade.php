<section>
    <header class="border-b border-red-100 pb-4 mb-6">
        <h2 class="text-lg font-bold text-red-600" style="font-family: 'Noto Serif Bengali', serif;">
            অ্যাকাউন্ট বন্ধ বা নিষ্ক্রিয়করণ (Account Deactivation)
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            নিরাপত্তা ও প্রাতিষ্ঠানিক ডেটা সংরক্ষণের স্বার্থে ব্যবহারকারীরা সরাসরি নিজেদের অ্যাকাউন্ট মুছে ফেলতে পারবেন না। 
        </p>
    </header>

    {{-- Informative Alert Block --}}
    <div class="p-5 rounded-2xl border border-amber-100 bg-amber-50/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="text-2xl mt-0.5">ℹ️</span>
            <div>
                <h4 class="font-bold text-gray-900 text-sm">আপনার কি অ্যাকাউন্টটি বন্ধ করা প্রয়োজন?</h4>
                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                    আপনার প্রোফাইলের সাথে নিবন্ধিত কর্মী, ভিসা প্রসেসিং বা অন্যান্য গুরুত্বপূর্ণ নথিপত্র যুক্ত থাকতে পারে। আপনি যদি সাময়িক বা স্থায়ীভাবে আপনার কার্যক্রম বন্ধ করতে চান, তবে অনুগ্রহ করে আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করুন। অ্যাডমিন প্যানেল থেকে আপনার অনুরোধটি যাচাই করে ব্যবস্থা নেওয়া হবে।
                </p>
            </div>
        </div>
        
        {{-- Fixed & Visible Contact Support Button --}}
        <a href="mailto:support@ameelhub.com" 
           class="w-full sm:w-auto text-center px-6 py-3 rounded-xl text-xs font-bold transition-all whitespace-nowrap shadow-sm block"
           style="background-color: #0B4F3F; color: #FFFFFF;"
           onmouseover="this.style.backgroundColor='#0e6350'"
           onmouseout="this.style.backgroundColor='#0B4F3F'">
            সাপোর্ট টিমে মেসেজ দিন
        </a>
    </div>
</section>