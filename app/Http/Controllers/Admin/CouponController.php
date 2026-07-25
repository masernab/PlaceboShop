<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Http\Resources\Admin\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        $coupons = Coupon::query()->orderBy('code')->get();

        return Inertia::render('admin/coupons/index', [
            'coupons' => CouponResource::collection($coupons),
        ]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        Coupon::query()->create($request->couponAttributes());

        return back();
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->couponAttributes());

        return back();
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back();
    }
}
